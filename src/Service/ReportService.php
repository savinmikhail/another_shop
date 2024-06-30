<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\OrderItem;
use Doctrine\ORM\EntityManagerInterface;

use function fclose;
use function fopen;
use function fwrite;
use function json_encode;

use const PHP_EOL;

final readonly class ReportService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function generateReportFile(string $reportId): void
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
