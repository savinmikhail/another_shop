<?php

declare(strict_types=1);

namespace App\Tests\Web;

use App\Enum\UserRole;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class BaseTestCase extends WebTestCase
{
    protected ?EntityManagerInterface $entityManager = null;
    protected ?UserPasswordHasherInterface $passwordHasher = null;
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        $client = static::createClient();
        $this->client = $client;
        $this->entityManager = $client->getContainer()->get('doctrine')->getManager();
        $this->passwordHasher = $client->getContainer()->get(UserPasswordHasherInterface::class);

        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

        // Drop and recreate tables for all entities
        if (!empty($metadata)) {
            $schemaTool->dropSchema($metadata);
            $schemaTool->createSchema($metadata);
        }

        // Create a user for authentication
        $this->createTestUser();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null; // Avoid memory leaks
    }

    private function createTestUser(): void
    {
        $user = new User();
        $user->setEmail('adminuser@example.com')
            ->setPassword($this->passwordHasher->hashPassword($user, 'adminpassword'))
            ->setRoles(['ROLE_ADMIN'])
            ->setPhone('1234567890')
            ->setName('name')
            ->setRole(UserRole::ADMIN);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
