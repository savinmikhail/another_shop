<?php

namespace App\Handler;

use App\DTO\Message;
use App\Manager\MessageManager;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\ExceptionInterface;

#[AsMessageHandler]
readonly class MessageHandler
{
    public function __construct(private MessageManager $messageManager)
    {
    }

    /**
     * @throws ExceptionInterface
     */
    public function __invoke(Message $message): void
    {
        $this->messageManager->createMessage($message->getText());
    }
}
