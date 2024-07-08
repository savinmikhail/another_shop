<?php

namespace App\Tests\Web\Controller;

use App\DTO\Message;
use App\Entity\User;
use App\Tests\Web\BaseTestCase;
use stdClass;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

class ReportControllerTest extends BaseTestCase
{
    private MessageBusInterface $messageBus;

    protected function setUp(): void
    {
        parent::setUp();

        $userRepository = $this->entityManager->getRepository(User::class);
        $testUser = $userRepository->findOneByEmail('adminuser@example.com');
        $this->client->loginUser($testUser);

        // Mock the MessageBusInterface
        $this->messageBus = $this->createMock(MessageBusInterface::class);

        // Override the service container to use the mock
        $this->client->getContainer()->set('messenger.bus.default', $this->messageBus);
    }

    public function testGenerateReportSuccess(): void
    {
        $this
            ->messageBus
            ->expects(self::once())
            ->method('dispatch')
            ->willReturn(new Envelope(new stdClass()));

        $this->client->request(
            'POST',
            '/api/report/generate',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_ACCEPTED, $response->getStatusCode());

        $this->assertJson($response->getContent());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('reportId', $responseData);

        $this->assertTrue(Uuid::isValid($responseData['reportId']));
    }
}
