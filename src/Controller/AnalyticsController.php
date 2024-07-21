<?php

declare(strict_types=1);

namespace App\Controller;

use App\Client\ClickHouse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class AnalyticsController extends AbstractController
{
    public function __construct(private readonly ClickHouse $clickHouse)
    {
    }

     #[Route("/api/admin/users-online-by-hour", name: "users_online_by_hour", methods: ['GET'])]
    public function getUsersOnlineByHour(): JsonResponse
    {
        $data = $this->clickHouse->getUsersOnlineByHour();
        return new JsonResponse($data);
    }
}
