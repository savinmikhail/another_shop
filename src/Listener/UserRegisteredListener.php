<?php

declare(strict_types=1);

namespace App\Listener;

use App\Enum\MessageType;
use App\Event\UserRegisteredEvent;
use App\Service\NotificationService;

final readonly class UserRegisteredListener
{
    public function __construct(
        private NotificationService $notificationService
    ) {
    }

    public function onUserRegistered(UserRegisteredEvent $event): void
    {
        $user = $event->getUser();

        $message = [
            'type' => MessageType::SMS,
            'userPhone' => $user->getPhone(),
            'userEmail' => $user->getEmail(),
            'promoId' => uniqid(),
        ];

        $this->notificationService->sendSMS($user->getPhone(), json_encode($message));
    }
}