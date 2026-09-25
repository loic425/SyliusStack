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

namespace App\Enum;

enum ApplicationStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
    case WAITING_LIST = 'waiting list';
}
