<?php

declare(strict_types=1);

use App\Entity\Application;
use App\Enum\ApplicationStatus;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('framework', [
        'workflows' => [
            'application' => [
                'type' => 'state_machine',
                 'initial_marking' => ApplicationStatus::DRAFT->value,
                'marking_store' => [
                    // Point at statusValue (string) rather than status (BackedEnum):
                    // Symfony < 7's MethodMarkingStore casts the property to string
                    // and does not support BackedEnum properties.
                    'property' => 'statusValue',
                ],
                'supports' => [
                    Application::class,
                ],
                'audit_trail' => [
                    'enabled' => true,
                ],
                'places' => [
                    ApplicationStatus::DRAFT->value,
                    ApplicationStatus::SUBMITTED->value,
                    ApplicationStatus::ACCEPTED->value,
                    ApplicationStatus::REJECTED->value,
                    ApplicationStatus::WAITING_LIST->value,
                    ApplicationStatus::CANCELLED->value,
                ],
                'transitions' => [
                    'submit' => [
                        'from' => ApplicationStatus::DRAFT->value,
                        'to' => ApplicationStatus::SUBMITTED->value,
                    ],
                    'accept' => [
                        'from' => ApplicationStatus::SUBMITTED->value,
                        'to' => ApplicationStatus::ACCEPTED->value,
                    ],
                    'reject' => [
                        'from' => ApplicationStatus::SUBMITTED->value,
                        'to' => ApplicationStatus::REJECTED->value,
                    ],
                    'hold' => [
                        'from' => ApplicationStatus::SUBMITTED->value,
                        'to' => ApplicationStatus::WAITING_LIST->value,
                    ],
                    'cancel' => [
                        'from' => [ApplicationStatus::DRAFT->value, ApplicationStatus::SUBMITTED->value, ApplicationStatus::ACCEPTED->value, ApplicationStatus::WAITING_LIST->value],
                        'to' => ApplicationStatus::CANCELLED->value,
                    ],
                ],
            ],
        ],
    ]);
};
