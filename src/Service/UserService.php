<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\User\UserEditRoleDTO;
use App\DTO\User\UserRegisterDTO;
use App\Entity\User;
use App\Event\UserRegisteredEvent;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class UserService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
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
            ->setRoles(['ROLE_USER']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $event = new UserRegisteredEvent($user);
        $this->eventDispatcher->dispatch($event, UserRegisteredEvent::NAME);
    }

    /**
     * @throws Exception
     */
    public function editRole(UserEditRoleDTO $editRoleDTO): void
    {
        $user = $this->entityManager->getRepository(User::class)->find($editRoleDTO->id);
        if (! $user) {
            throw new Exception('Such user does not exist');
        }
        $user->setRoles([$editRoleDTO->role]);
        $this->entityManager->flush();
    }
}
