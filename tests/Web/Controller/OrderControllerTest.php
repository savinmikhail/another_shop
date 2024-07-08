<?php

namespace App\Tests\Web\Controller;

use App\DTO\Cart\AddToCartDTO;
use App\DTO\Order\CreateOrderDTO;
use App\Entity\Product;
use App\Entity\User;
use App\Enum\DeliveryType;
use App\Enum\OrderStatus;
use App\Service\CartService;
use App\Service\OrderService;
use App\Tests\Web\BaseTestCase;
use Symfony\Component\HttpFoundation\Response;
use function dd;
use function json_encode;

class OrderControllerTest extends BaseTestCase
{
    private CartService $cartService;
    private OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();

        $userRepository = $this->entityManager->getRepository(User::class);
        $testUser = $userRepository->findOneByEmail('adminuser@example.com');
        $this->client->loginUser($testUser);

        $this->cartService = $this->client->getContainer()->get(CartService::class);
        $this->orderService = $this->client->getContainer()->get(OrderService::class);

        $productRepository = $this->entityManager->getRepository(Product::class);
        $product = $productRepository->findAll()[0];

        $this->cartService->add(new AddToCartDTO($product->getId()), $testUser);
    }

    public function testCreateOrderSuccess(): void
    {
        $payload = [
            'deliveryType' => DeliveryType::SELF_DELIVERY,
            'addressId' => 1,
        ];

        $this->client->request(
            'POST',
            '/api/order',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertStringContainsString('Order created', $this->client->getResponse()->getContent());
    }

    public function testCreateOrderValidationFailure(): void
    {
        $payload = [
            'deliveryType' => '',  // Invalid value to trigger validation error
            'addressId' => -1,    // Invalid value to trigger validation error
        ];

        $this->client->request(
            'POST',
            '/api/order',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertStringContainsString('error', $this->client->getResponse()->getContent());
    }

    public function testUpdateOrderStatusSuccess(): void
    {
        $productRepository = $this->entityManager->getRepository(Product::class);
        $product = $productRepository->findAll()[0];

        $userRepository = $this->entityManager->getRepository(User::class);
        $testUser = $userRepository->findOneByEmail('adminuser@example.com');
        $this->cartService->add(new AddToCartDTO($product->getId()), $testUser);
        $this->orderService->create(new CreateOrderDTO(DeliveryType::COURIER->value, 1), $testUser);
        $payload = [
            'status' => OrderStatus::COMPLETED,
            'id' => 1,
        ];

        $this->client->request(
            'PATCH',
            '/api/admin/order',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertStringContainsString('order status was updated', $this->client->getResponse()->getContent());
    }

    public function testUpdateOrderStatusValidationFailure(): void
    {
        $payload = [
            'status' => '',  // Invalid value to trigger validation error
            'id' => -1,      // Invalid value to trigger validation error
        ];

        $this->client->request(
            'PATCH',
            '/api/admin/order',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertStringContainsString('error', $this->client->getResponse()->getContent());
    }
}
