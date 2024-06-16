<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\CreateOrderDTO;
use App\DTO\UpdateOrderStatusDTO;
use App\Entity\Order;
use App\Entity\User;
use App\Enum\OrderStatus;
use App\Service\OrderService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function json_decode;

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
    ) {
        try {
            $this->orderService->updateStatus($updateOrderStatusDTO);
            return $this->json(['order status was updated']);
        } catch (Exception $e) {
            return $this->json($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}
