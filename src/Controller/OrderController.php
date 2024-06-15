<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Order;
use App\Entity\User;
use App\Enum\OrderStatus;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function json_decode;
use function json_encode;

final class OrderController extends AbstractController
{
    #[Route('/api/order', name: 'create_order', methods: ['POST'])]
    public function createOrder(
        Request $request,
        EntityManagerInterface $em,
        NotificationService $notificationService
    ): Response {
        $data = json_decode($request->getContent(), true);
        /** @var User $user */
        $user = $this->getUser();
        $cart = $user->getCart();
        if (! $cart) {
            return $this->json('empty cart', Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $items = $cart->getCartItem();
        if ($items->count() < 1) {
            return $this->json(['nothing to purchase'], Response::HTTP_BAD_REQUEST);
        }
        if ($items->count() > 20) {
            return $this->json(['you cannot purchase more than 20 items per once'], Response::HTTP_BAD_REQUEST);
        }
        if (! $data['deliveryType']) {
            return $this->json(['choose delivery type'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order = new Order();
        $order
            ->setStatus(OrderStatus::PAYED)
            ->setOwner($user);
        $em->persist($order);
        $em->flush();

        $notificationService->sendEmail($this->generateNotification());

        return $this->json(['status' => 'Order created']);
    }

    private function generateNotification(): string
    {
        return json_encode(['todo: implement']);
    }

    #[Route('/api/admin/order', name: 'change_order_status', methods: ['PATCH'])]
    public function updateStatus(
        Request $request,
        EntityManagerInterface $em,
    )
    {
        $data = json_decode($request->getContent(), true);

        $order = $em->getRepository(Order::class)->find($data['id']);
        $order->setStatus($data['status']);
        $em->persist($order);
        $em->flush();
        return $this->json(['order status set to '. $order->getStatus()]);
    }
}
