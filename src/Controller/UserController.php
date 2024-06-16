<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\UserRole;
use App\Event\UserRegisteredEvent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use function json_decode;

final class UserController extends AbstractController
{
    public function __construct(private readonly EventDispatcherInterface $eventDispatcher)
    {
    }

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setEmail($data['email'])
            ->setPhone($data['phone'])
            ->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $data['password']
                )
            )
            ->setRole(UserRole::USER);

        $em->persist($user);
        $em->flush();

        $event = new UserRegisteredEvent($user);
        $this->eventDispatcher->dispatch($event, UserRegisteredEvent::NAME);

        return new JsonResponse(['message' => 'User registered successfully'], Response::HTTP_CREATED);
    }

    #[Route('/api/admin/user', name: 'admin_user_edit', methods: ['PATCH'])]
    public function edit(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $user = $em->getRepository(User::class)->find($data['id']);
        $user->setRole(UserRole::tryFrom($data['role']));
        $em->persist($user);
        $em->flush();
        return new JsonResponse(['message' => 'User data saved successfully'], Response::HTTP_OK);
    }
}
