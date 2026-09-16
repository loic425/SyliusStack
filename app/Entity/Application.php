<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Entity;

use App\Enum\ApplicationStatus;
use App\Grid\ApplicationGrid;
use App\Repository\ApplicationRepository;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Resource\Metadata\ApplyStateMachineTransition;
use Sylius\Resource\Metadata\AsResource;
use Sylius\Resource\Metadata\Index;

#[ORM\Entity(repositoryClass: ApplicationRepository::class)]
#[AsResource(
    section: 'admin',
    templatesDir: '@SyliusAdminUi/crud',
    routePrefix: '/admin',
    operations: [
        new Index(grid: ApplicationGrid::class),
        new ApplyStateMachineTransition(stateMachineTransition: 'submit'),
        new ApplyStateMachineTransition(stateMachineTransition: 'accept'),
        new ApplyStateMachineTransition(stateMachineTransition: 'reject'),
        new ApplyStateMachineTransition(stateMachineTransition: 'hold'),
        new ApplyStateMachineTransition(stateMachineTransition: 'cancel'),
    ],
)]
class Application implements ResourceInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $submittedAt = null;

    #[ORM\OneToOne(inversedBy: 'application', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Talk $talk = null;

    #[ORM\Column(enumType: ApplicationStatus::class)]
    private ?ApplicationStatus $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubmittedAt(): ?\DateTimeImmutable
    {
        return $this->submittedAt;
    }

    public function setSubmittedAt(\DateTimeImmutable $submittedAt): static
    {
        $this->submittedAt = $submittedAt;

        return $this;
    }

    public function getTalk(): ?Talk
    {
        return $this->talk;
    }

    public function setTalk(Talk $talk): static
    {
        $this->talk = $talk;

        return $this;
    }

    public function getStatus(): ?ApplicationStatus
    {
        return $this->status;
    }

    public function setStatus(ApplicationStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * String-typed accessor used by Symfony Workflow's MethodMarkingStore.
     *
     * Symfony < 7 does not support BackedEnum properties in the default
     * marking store and attempts to cast the enum to string, which throws.
     * Exposing the backed value keeps the domain API enum-typed while
     * remaining compatible with Symfony 6.4.
     */
    public function getStatusValue(): ?string
    {
        return $this->status?->value;
    }

    public function setStatusValue(string $value): void
    {
        $this->status = ApplicationStatus::from($value);
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
