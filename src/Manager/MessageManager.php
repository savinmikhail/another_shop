<?php

namespace App\Manager;

use App\DTO\Message;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class MessageManager
{
    public function __construct(
        private MessageBusInterface $messageBus,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    public function createMessage(string $reportId): void
    {
        $this->messageBus->dispatch(new Message($reportId));
    }
}
