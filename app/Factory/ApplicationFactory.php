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

namespace App\Factory;

use App\Entity\Application;
use App\Entity\Talk;
use App\Enum\ApplicationStatus;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Application>
 */
final class ApplicationFactory extends PersistentObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return Application::class;
    }

    public function withSubmittedAt(\DateTimeImmutable $submittedAt): self
    {
        return $this->with(['submittedAt' => $submittedAt]);
    }

    public function withTalk(Talk $talk): self
    {
        return $this->with(['talk' => $talk]);
    }

    public function withStatus(ApplicationStatus $status): self
    {
        return $this->with(['status' => $status]);
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'status' => self::faker()->randomElement(ApplicationStatus::cases()),
            'submittedAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'talk' => TalkFactory::new(),
            'updatedAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }
}
