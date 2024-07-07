<?php

namespace App\Tests\Web\Controller;

use App\Entity\User;
use App\Tests\Web\BaseTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends BaseTestCase
{
    public function testRegisterSuccess(): void
    {
        $payload = [
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'password' => 'securepassword',
            'name' => 'Test User',
        ];

        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );
        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertStringContainsString('User registered successfully', $this->client->getResponse()->getContent());
    }

    public function testRegisterValidationFailure(): void
    {
        // Payload with missing required fields
        $payload = [
            'email' => '',
            'phone' => '1234567890',
            'password' => 'securepassword',
            'name' => 'Test User',
        ];

        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertStringContainsString('error', $this->client->getResponse()->getContent());
    }

    public function testEditUserSuccess(): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $testUser = $userRepository->findOneByEmail('adminuser@example.com');
        $this->client->loginUser($testUser);

        $payload = [
            'role' => 1,
            'id' => 1,
        ];

        $this->client->request(
            'PATCH',
            '/api/admin/user',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertStringContainsString('User data saved successfully', $this->client->getResponse()->getContent());
    }

    public function testEditUserValidationFailure(): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $testUser = $userRepository->findOneByEmail('adminuser@example.com');
        $this->client->loginUser($testUser);

        $payload = [
            'role' => 'invalid',  // Invalid value to trigger validation error
            'id' => 1,
        ];

        $this->client->request(
            'PATCH',
            '/api/admin/user',
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
