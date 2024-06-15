<?php

declare(strict_types=1);

namespace App\Service;

final readonly class NotificationService
{
    public function sendEmail(string $message): void
    {
        //send to responsible microservice
    }

    public function sendSMS(string $message)
    {
        //send to responsible microservice
    }
}
