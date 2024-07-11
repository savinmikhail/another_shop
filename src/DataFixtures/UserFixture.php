<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture
{
    public function __construct(protected UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail('adminuser@example.com')
            ->setPassword($this->passwordHasher->hashPassword($admin, 'adminpassword'))
            ->setRoles(['ROLE_ADMIN'])
            ->setPhone('1234567890')
            ->setName('name')
            ->setRoles(['ROLE_ADMIN']);

        $user = new User();
        $user->setEmail('user@example.com')
            ->setPassword($this->passwordHasher->hashPassword($user, 'password'))
            ->setRoles(['ROLE_ADMIN'])
            ->setPhone('0987654321')
            ->setName('name')
            ->setRoles(['ROLE_USER']);

        $manager->persist($admin);
        $manager->persist($user);
        $manager->flush();
    }
}
