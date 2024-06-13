<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\CartItem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ReportController extends AbstractController
{
    #[Route('/api/report/generate', name: 'generate_report', methods: ['POST'])]
    public function generateReport(EntityManagerInterface $em, HttpClientInterface $httpClient): JsonResponse
    {
        $reportId = Uuid::v4()->toRfc4122();

        $this->generateReportAsync($reportId, $em, $httpClient);

        return new JsonResponse(['reportId' => $reportId], Response::HTTP_ACCEPTED);
    }

    private function generateReportAsync(string $reportId, EntityManagerInterface $em, HttpClientInterface $httpClient): void
    {
        $reportFilePath = $this->generateReportFile($reportId, $em);

        // Send an event to Kafka about the report generation status
        $result = [
            'reportId' => $reportId,
            'result' => 'success',
            'detail' => []
        ];

//        $httpClient->request('POST', 'http://kafka-service/notify', [
//            'json' => $result
//        ]);
    }

    private function generateReportFile(string $reportId, EntityManagerInterface $em): string
    {
        $soldItems = $em->getRepository(CartItem::class)->findAll();

        $filePath = __DIR__ . '/../../var/reports/' . $reportId . '.jsonl';
        $fileHandle = fopen($filePath, 'w');

        foreach ($soldItems as $item) {
            $reportLine = json_encode([
                'product_name' => $item->getProduct()->getName(),
                'price' => $item->getCost(),
                'amount' => $item->getQuantity(),
                'user' => [
                    'id' => $item->getCart()->getOwner()->getId()
                ]
            ]);

            fwrite($fileHandle, $reportLine . PHP_EOL);
        }

        fclose($fileHandle);

        return $filePath;
    }
}