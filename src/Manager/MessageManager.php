<?php

namespace App\Manager;

use App\DTO\Message;
use App\Entity\OrderItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

use function fclose;
use function fopen;
use function fwrite;
use function json_encode;

use const PHP_EOL;

class MessageManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function createMessage(string $reportId): Message
    {
        $this->generateReportFile($reportId);
        $this->messageBus->dispatch(new Message($reportId));
    }

    private function generateReportFile(string $reportId): void
    {
        $soldItems = $this->entityManager->getRepository(OrderItem::class)->findAll();

        $filePath = __DIR__ . '/../../var/reports/' . $reportId . '.jsonl';
        $fileHandle = fopen($filePath, 'w');

        foreach ($soldItems as $item) {
            $reportLine = json_encode([
                'product_name' => $item->getProduct()->getName(),
                'price' => $item->getCost(),
                'amount' => $item->getQuantity(),
                'user' => [
                    'id' => $item->getOrder()->getOwner()->getId()
                ]
            ]);

            fwrite($fileHandle, $reportLine . PHP_EOL);
        }

        fclose($fileHandle);
    }
}
