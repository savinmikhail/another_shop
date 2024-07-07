<?php

declare(strict_types=1);

namespace App\Tests\Web;

use App\DataFixtures\AddressFixture;
use App\DataFixtures\ProductFixture;
use App\DataFixtures\UserFixture;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class BaseTestCase extends WebTestCase
{
    protected ?EntityManagerInterface $entityManager = null;
    protected UserPasswordHasherInterface $passwordHasher;
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

        // Load fixtures
        $loader = new Loader();
        $loader->addFixture(new ProductFixture());
        $loader->addFixture(new UserFixture($this->passwordHasher));
        $loader->addFixture(new AddressFixture());

        $purger = new ORMPurger();
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute($loader->getFixtures());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null; // Avoid memory leaks
    }
}
