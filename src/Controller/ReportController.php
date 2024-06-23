<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

final class ReportController extends AbstractController
{
    public function __construct(private readonly MessageBusInterface $messageBus)
    {
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route('/api/report/generate', name: 'generate_report', methods: ['POST'])]
    public function generateReport(): JsonResponse
    {
        $reportId = Uuid::v4()->toRfc4122();
        $this->messageBus->dispatch(new Message($reportId));

        return new JsonResponse(['reportId' => $reportId], Response::HTTP_ACCEPTED);
    }
}
