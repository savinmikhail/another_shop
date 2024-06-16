<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\User\UserRegisterDTO;
use App\Entity\User;
use App\Enum\UserRole;
use App\Event\UserRegisteredEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class UserService
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function register(UserRegisterDTO $userRegisterDTO): void
    {
        $user = new User();
        $user->setEmail($userRegisterDTO->email)
            ->setPhone($userRegisterDTO->phone)
            ->setPassword(
                $this->passwordHasher->hashPassword(
                    $user,
                    $userRegisterDTO->password
                )
            )
            ->setName($userRegisterDTO->name)
            ->setRole(UserRole::USER);

        $this->em->persist($user);
        $this->em->flush();

        $event = new UserRegisteredEvent($user);
        $this->eventDispatcher->dispatch($event, UserRegisteredEvent::NAME);
    }
}
