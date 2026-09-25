<?php

declare(strict_types=1);

namespace MainTests\Sylius\Functional;

use App\Enum\ApplicationStatus;
use App\Factory\ApplicationFactory;
use App\Factory\TalkFactory;
use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Attribute\ResetDatabase;

#[ResetDatabase]
final class ApplicationTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();

        $user = UserFactory::new()
            ->admin()
            ->create()
        ;

        $this->client->loginUser($user);
    }

    public function testBrowsingApplications(): void
    {
        $talk1 = TalkFactory::new()
            ->withTitle('Sylius St(AI)ck : integrate AI into the Sylius Stack')
            ->create()
        ;
        $talk2 = TalkFactory::new()
            ->withTitle('Sylius Stack 4 the win!')
            ->create()
        ;

        ApplicationFactory::new()
            ->withSubmittedAt(new \DateTimeImmutable('2026-11-13 18:00:00'))
            ->withStatus(ApplicationStatus::SUBMITTED)
            ->withTalk($talk1)
            ->create()
        ;

        ApplicationFactory::new()
            ->withSubmittedAt(new \DateTimeImmutable('2025-11-03 18:00:00'))
            ->withStatus(ApplicationStatus::ACCEPTED)
            ->withTalk($talk2)
            ->create()
        ;

        $this->client->request(Request::METHOD_GET, '/admin/applications');

        $this->assertResponseIsSuccessful();

        // Validate Header
        $this->assertSelectorTextContains('[data-test-page-title]', 'Applications');

        // Validate Table header
        $this->assertSelectorTextContains('.sylius-table-column-talk', 'Talk');
        $this->assertSelectorTextContains('.sylius-table-column-speakers', 'Speakers');
        $this->assertSelectorTextContains('.sylius-table-column-status', 'Status');
        $this->assertSelectorTextContains('.sylius-table-column-submittedAt', 'Submitted at');
        $this->assertSelectorTextContains('.sylius-table-column-updatedAt', 'Updated at');
        $this->assertSelectorTextContains('.sylius-table-column-actions', 'Actions');

        // Validate Table data
        $this->assertSelectorTextContains('tr.item:first-child', 'Sylius St(AI)ck : integrate AI into the Sylius Stack');
        $this->assertSelectorTextContains('tr.item:first-child', 'submitted');
        $this->assertSelectorTextContains('tr.item:first-child', '2026-11-13 18:00:00');
        $this->assertSelectorTextContains('tr.item:first-child', '');
        $this->assertSelectorExists('tr.item:first-child [data-bs-title=Accept]');
        $this->assertSelectorExists('tr.item:first-child [data-bs-title=Reject]');
        $this->assertSelectorExists('tr.item:first-child [data-bs-title=Cancel]');

        $this->assertSelectorTextContains('tr.item:last-child', 'Sylius Stack 4 the win!');
        $this->assertSelectorTextContains('tr.item:last-child', '2025-11-03 18:00:00');
        $this->assertSelectorTextContains('tr.item:last-child', 'accepted');
        $this->assertSelectorExists('tr.item:last-child [data-bs-title=Cancel]');
    }

    public function testAcceptingApplication(): void
    {
        $talk = TalkFactory::new()->withTitle('My fantastic talk')->create();

        $application = ApplicationFactory::new()
            ->withTalk($talk)
            ->withStatus(ApplicationStatus::SUBMITTED)
            ->create()
        ;

        $this->client->request(Request::METHOD_GET, '/admin/applications');
        $this->assertResponseIsSuccessful();

        // Test action buttons are available with right icons
        $this->assertSelectorExists('tr.item:first-child [data-bs-title=Accept]');
        $this->assertSelectorExists('tr.item:first-child [data-bs-title=Reject]');
        $this->assertSelectorExists('tr.item:first-child [data-bs-title=Cancel]');

        $this->client->request(Request::METHOD_PUT, sprintf('/admin/applications/%s/accept', $application->getId()));

        $this->assertResponseRedirects(expectedCode: Response::HTTP_FOUND);

        $this->client->request(Request::METHOD_GET, '/admin/applications');

        // Action buttons should be removed if workflow makes them impossible
        $this->assertSelectorNotExists('tr.item:first-child [data-bs-title=Accept]');
        $this->assertSelectorNotExists('tr.item:first-child [data-bs-title=Reject]');
        $this->assertSelectorNotExists('tr.item:first-child [data-bs-title="Place on waiting list"]');
        // But actions that are still available should remain displayed
        $this->assertSelectorExists('tr.item:first-child [data-bs-title=Cancel]');

        // Test flash message
        $this->assertSelectorTextContains('[data-test-sylius-flash-message]', 'Application has been successfully updated.');

        $application = ApplicationFactory::find(['talk' => $talk]);

        $this->assertSame(ApplicationStatus::ACCEPTED, $application->getStatus());
    }

    public function testRejectingApplication(): void
    {
        $talk = TalkFactory::new()->withTitle('My fantastic talk')->create();

        $application = ApplicationFactory::new()
            ->withTalk($talk)
            ->withStatus(ApplicationStatus::SUBMITTED)
            ->create()
        ;

        $this->client->request(Request::METHOD_GET, '/admin/applications');
        $this->assertResponseIsSuccessful();

        // Test action buttons are available with right icons
        $this->assertSelectorExists('tr.item:first-child [data-bs-title=Accept]');
        $this->assertSelectorExists('tr.item:first-child [data-bs-title=Reject]');
        $this->assertSelectorExists('tr.item:first-child [data-bs-title="Place on waiting list"]');
        $this->assertSelectorExists('tr.item:first-child [data-bs-title=Cancel]');

        $this->client->request(Request::METHOD_PUT, sprintf('/admin/applications/%s/reject', $application->getId()));

        $this->assertResponseRedirects(expectedCode: Response::HTTP_FOUND);

        $this->client->request(Request::METHOD_GET, '/admin/applications');

        // Action buttons should be removed if workflow makes them impossible
        $this->assertSelectorNotExists('tr.item:first-child [data-bs-title=Accept]');
        $this->assertSelectorNotExists('tr.item:first-child [data-bs-title=Reject]');
        $this->assertSelectorNotExists('tr.item:first-child [data-bs-title="Place on waiting list"]');
        $this->assertSelectorNotExists('tr.item:first-child [data-bs-title=Cancel]');

        // Test flash message
        $this->assertSelectorTextContains('[data-test-sylius-flash-message]', 'Application has been successfully updated.');

        $application = ApplicationFactory::find(['talk' => $talk]);

        $this->assertSame(ApplicationStatus::REJECTED, $application->getStatus());

    }

    public function testPuttingApplicationOnWaitingList(): void
    {
        $talk = TalkFactory::new()->withTitle('My fantastic talk I might not give')->create();

        $application = ApplicationFactory::new()
            ->withTalk($talk)
            ->withStatus(ApplicationStatus::SUBMITTED)
            ->create()
        ;

        $this->client->request(Request::METHOD_GET, '/admin/applications');
        $this->assertResponseIsSuccessful();

        // Test action buttons are available with right icons
        $this->assertSelectorExists('tr.item:first-child [data-bs-title=Accept]');
        $this->assertSelectorExists('tr.item:first-child [data-bs-title=Reject]');
        $this->assertSelectorExists('tr.item:first-child [data-bs-title="Place on waiting list"]');
        $this->assertSelectorExists('tr.item:first-child [data-bs-title=Cancel]');

        $this->client->request(Request::METHOD_PUT, sprintf('/admin/applications/%s/hold', $application->getId()));

        $this->assertResponseRedirects(expectedCode: Response::HTTP_FOUND);

        $this->client->request(Request::METHOD_GET, '/admin/applications');

        // Action buttons should be removed if workflow makes them impossible
        $this->assertSelectorNotExists('tr.item:first-child [data-bs-title=Accept]');
        $this->assertSelectorNotExists('tr.item:first-child [data-bs-title=Reject]');
        $this->assertSelectorNotExists('tr.item:first-child [data-bs-title="Place on waiting list"]');
        // But actions that are still available should remain displayed
        $this->assertSelectorExists('tr.item:first-child [data-bs-title=Cancel]');

        // Test flash message
        $this->assertSelectorTextContains('[data-test-sylius-flash-message]', 'Application has been successfully updated.');

        $application = ApplicationFactory::find(['talk' => $talk]);

        $this->assertSame(ApplicationStatus::WAITING_LIST, $application->getStatus());

    }

    public function testCancellingApplication(): void
    {
        $talk = TalkFactory::new()->withTitle('My incredible talk')->create();

        $application = ApplicationFactory::new()
            ->withStatus(ApplicationStatus::WAITING_LIST)
            ->withTalk($talk)
            ->create()
        ;

        $this->client->request(Request::METHOD_GET, '/admin/applications');
        $this->assertResponseIsSuccessful();

        $this->client->request(Request::METHOD_PUT, sprintf('/admin/applications/%s/cancel', $application->getId()));

        $this->assertResponseRedirects(expectedCode: Response::HTTP_FOUND);

        $this->client->request(Request::METHOD_GET, '/admin/applications');

        // Action buttons should be removed if workflow makes them impossible
        $this->assertSelectorNotExists('tr.item:first-child [data-bs-title=Accept]');
        $this->assertSelectorNotExists('tr.item:first-child [data-bs-title=Reject]');
        $this->assertSelectorNotExists('tr.item:first-child [data-bs-title="Place on waiting list"]');
        $this->assertSelectorNotExists('tr.item:first-child [data-bs-title=Cancel]');

        // Test flash message
        $this->assertSelectorTextContains('[data-test-sylius-flash-message]', 'Application has been successfully updated.');

        $application = ApplicationFactory::find(['talk' => $talk]);

        $this->assertSame(ApplicationStatus::CANCELLED, $application->getStatus());
    }
}
