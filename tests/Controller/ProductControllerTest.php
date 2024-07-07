<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

use function json_decode;

class ProductControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/products');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $this->assertTrue($client->getResponse()->headers->contains('Content-Type', 'application/json'));

        $content = $client->getResponse()->getContent();
        $data = json_decode($content, true);

        $this->assertIsArray($data);
    }

    public function testCreateProductSuccess(): void
    {
        $client = static::createClient();

        $payload = [
            'name' => 'Test Product',
            'measurements' => [
                'height' => 10,
                'width' => 5,
                'length' => 2,
                'weight' => 1,
            ],
            'description' => 'This is a test product.',
            'cost' => 100,
            'tax' => 10,
            'version' => 1,
        ];

        $client->request(
            'POST',
            '/api/products',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        $this->assertStringContainsString('product was added successfully', $client->getResponse()->getContent());
    }

    public function testCreateProductValidationFailure(): void
    {
        $client = static::createClient();

        // Payload with missing required fields
        $payload = [
            'name' => '',
            'measurements' => [
                'height' => -10,  // Invalid value to trigger validation error
                'width' => 5,
                'length' => 2,
                'weight' => 1,
            ],
            'description' => 'This is a test product.',
            'cost' => -100,  // Invalid value to trigger validation error
            'tax' => 10,
            'version' => 1,
        ];

        $client->request(
            'POST',
            '/api/products',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
    }
}
