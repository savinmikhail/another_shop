<?php

namespace App\Tests\Web\Controller;

use App\Entity\User;
use App\Tests\Web\BaseTestCase;
use Symfony\Component\HttpFoundation\Response;

class AddressControllerTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $userRepository = $this->entityManager->getRepository(User::class);
        $testUser = $userRepository->findOneByEmail('adminuser@example.com');
        $this->client->loginUser($testUser);
    }

    public function testCreateAddressSuccess(): void
    {
        $payload = [
            'fullAddress' => '123 Main St, Anytown, USA',
            'kladrId' => 123456,
        ];

        $this->client->request(
            'POST',
            '/api/address',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertStringContainsString('Address was saved successfully', $this->client->getResponse()->getContent());
    }

    public function testCreateAddressValidationFailure(): void
    {
        // Payload with invalid data to trigger validation errors
        $payload = [
            'fullAddress' => 123,  // Invalid value to trigger validation error
            'kladrId' => -1,       // Invalid value to trigger validation error
        ];

        $this->client->request(
            'POST',
            '/api/address',
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
