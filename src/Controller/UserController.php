<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Order\CreateOrderDTO;
use App\DTO\User\UserRegisterDTO;
use App\Enum\UserRole;
use App\Service\UserService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use function json_decode;

final class UserController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
    ) {
    }

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        #[MapRequestPayload] UserRegisterDTO $userRegisterDTO
    ): JsonResponse {

        try {
            $this->userService->register($userRegisterDTO);
            return new JsonResponse(['message' => 'User registered successfully'], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return $this->json($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
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
