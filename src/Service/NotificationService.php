<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;

final readonly class NotificationService
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function sendEmail(string $message): void
    {
        $this->logger->info('Sending email', ['message' => $message]);
        //send to responsible microservice
    }

    public function sendSMS(string $message): void
    {
        $this->logger->info('Sending SMS', ['message' => $message]);
        //send to responsible microservice
    }
}
