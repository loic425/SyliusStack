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

namespace App\Story;

use App\Factory\ApplicationFactory;
use Zenstruck\Foundry\Story;

final class DefaultApplicationsStory extends Story
{
    public function build(): void
    {
        ApplicationFactory::createMany(20);
    }
}
