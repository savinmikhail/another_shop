<?php

namespace App\Tests\Web\Controller;

use App\Tests\Web\BaseTestCase;
use Symfony\Component\HttpFoundation\Response;

use function json_decode;

class ProductControllerTest extends BaseTestCase
{
    public function testIndex(): void
    {
        $this->client->request('GET', '/api/products');

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'));

        $content = $this->client->getResponse()->getContent();
        $data = json_decode($content, true);

        $this->assertIsArray($data);
    }

    public function testCreateProductSuccess(): void
    {
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

        $this->client->request(
            'POST',
            '/api/products',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertStringContainsString('product was added successfully', $this->client->getResponse()->getContent());
    }

    public function testCreateProductValidationFailure(): void
    {
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

        $this->client->request(
            'POST',
            '/api/products',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());
    }
}
