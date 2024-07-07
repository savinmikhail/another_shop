<?php

namespace App\Tests\Web\Controller;

use App\Entity\User;
use App\Tests\Web\BaseTestCase;
use Symfony\Component\HttpFoundation\Response;

class CartControllerTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $userRepository = $this->entityManager->getRepository(User::class);
        $testUser = $userRepository->findOneByEmail('adminuser@example.com');
        $this->client->loginUser($testUser);
    }

    public function testAddToCartSuccess(): void
    {
        $payload = [
            'productId' => 1,
        ];

        $this->client->request(
            'POST',
            '/api/carts',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertStringContainsString('added to cart successfully', $this->client->getResponse()->getContent());
    }

    public function testAddToCartValidationFailure(): void
    {
        $payload = [
            'productId' => -1,  // Invalid value
        ];

        $this->client->request(
            'POST',
            '/api/carts',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertStringContainsString('error', $this->client->getResponse()->getContent());
    }

    public function testShowCartSuccess(): void
    {
        $this->client->request(
            'GET',
            '/api/carts',
        );

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());
    }
}
