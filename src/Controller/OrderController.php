<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Order\CreateOrderDTO;
use App\DTO\Order\UpdateOrderStatusDTO;
use App\Entity\User;
use App\Service\OrderService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

final class OrderController extends AbstractController
{
    public function __construct(private readonly OrderService $orderService)
    {
    }

    #[Route('/api/order', name: 'create_order', methods: ['POST'])]
    public function createOrder(
        #[MapRequestPayload] CreateOrderDTO $createOrderDTO
    ): Response {

        /** @var User $user */
        $user = $this->getUser();

        try {
            $this->orderService->create($createOrderDTO, $user);
            return $this->json(['status' => 'Order created']);
        } catch (Exception $e) {
            return $this->json($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/api/admin/order', name: 'change_order_status', methods: ['PATCH'])]
    public function updateStatus(
        #[MapRequestPayload] UpdateOrderStatusDTO $updateOrderStatusDTO
    ): Response {
        try {
            $this->orderService->updateStatus($updateOrderStatusDTO);
            return $this->json(['order status was updated']);
        } catch (Exception $e) {
            return $this->json($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}
