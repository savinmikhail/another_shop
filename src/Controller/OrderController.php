<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Order;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function json_decode;

final class OrderController extends AbstractController
{
    #[Route('/api/order', name: 'create_order', methods: ['POST'])]
    public function createOrder(
        Request $request,
        EntityManagerInterface $em,
        NotificationService $notificationService
    ): Response {
        $data = json_decode($request->getContent(), true);

        $order = new Order();

        $em->persist($order);
        $em->flush();

        $notificationService->sendEmail($data['userEmail'], 'Order Created', 'Your order has been created.');

        return $this->json(['status' => 'Order created']);
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
