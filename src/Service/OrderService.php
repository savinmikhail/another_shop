<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CreateOrderDTO;
use App\DTO\UpdateOrderStatusDTO;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\User;
use App\Enum\DeliveryType;
use App\Enum\MessageType;
use App\Enum\NotificationType;
use App\Enum\OrderStatus;
use App\Repository\AddressRepository;
use Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

use function json_encode;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_UNICODE;

final readonly class OrderService
{
    public function __construct(
        private EntityManagerInterface $em,
        private NotificationService $notificationService,
        private AddressRepository $addressRepository,
    ) {
    }

    /**
     * @throws Exception
     */
    public function create(CreateOrderDTO $createOrderRequest, User $user): void
    {
        $cart = $user->getCart();
        if (! $cart) {
            throw new UnprocessableEntityHttpException('Empty cart.');
        }
        $items = $cart->getCartItem();
        $count = $items->count();
        if ($count < 1) {
            throw new BadRequestHttpException('Nothing to purchase.');
        }
        if ($count > 20) {
            throw new BadRequestHttpException('You cannot purchase more than 20 items per once.');
        }
        $deliveryType = DeliveryType::tryFrom($createOrderRequest->deliveryType);
        if (! $deliveryType) {
            throw new UnprocessableEntityHttpException('Incorrect delivery type.');
        }
        $address = $this->addressRepository->find($createOrderRequest->addressId);
        if (! $address && $deliveryType === DeliveryType::COURIER) {
            throw new UnprocessableEntityHttpException('Such address does not exist.');
        }
        $this->em->beginTransaction();
        try {
            $order = new Order();
            $order
                ->setStatus(OrderStatus::PAYED)
                ->setOwner($user);
            $this->em->persist($order);

            foreach ($items as $cartItem) {
                $orderItem = new OrderItem();
                $orderItem
                    ->setProduct($cartItem->getProduct())
                    ->setQuantity($cartItem->getQuantity())
                    ->setCost($cartItem->getCost());

                $order->addOrderItem($orderItem);
                $this->em->persist($orderItem);
                $cart->removeCartItem($cartItem);
            }
            $order->setDeliveryType($deliveryType);
            $order->setDeliveryAddress($address);
            $this->em->flush();
            $this->em->getConnection()->commit();

            $this->notificationService->sendEmail($this->generateNotification($user, $order));
        } catch (Exception $e) {
            $this->em->rollBack();
            throw $e;
        }
    }

    private function generateNotification(User $user, Order $order): string
    {
        $orderItems = $order->getOrderItems()->map(static function (OrderItem $item): array {
            return [
                'name' => $item->getProduct()->getName(),
                'cost' => $item->getCost(),
                'additionalInfo' => $item->getProduct()->getDescription()
            ];
        })->toArray();

        $message = [
            'type' => MessageType::EMAIL->value,
            'userPhone' => $user->getPhone(),
            'userEmail' => $user->getEmail(),
            'notificationType' => NotificationType::SUCCESS_PAYMENT,
            'orderNum' => (string) $order->getId(),
            'orderItems' => $orderItems,
            'deliveryType' => $order->getDeliveryType()->value,
            'deliveryAddress' => [
                'kladrId' => $order->getDeliveryAddress()->getKladrId(),
                'fullAddress' => $order->getDeliveryAddress()->getFullAddress(),
            ],
        ];

        return json_encode($message, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function updateStatus(UpdateOrderStatusDTO $updateOrderStatusDTO): void
    {
        $order = $this->em->getRepository(Order::class)->find($updateOrderStatusDTO->id);
        if (! $order) {
            throw new UnprocessableEntityHttpException('No such order');
        }
        $status = OrderStatus::tryFrom($updateOrderStatusDTO->status);
        if (! $status) {
            throw new UnprocessableEntityHttpException('Invalid status');
        }
        $order->setStatus($status);
        $this->em->flush();
    }
}
