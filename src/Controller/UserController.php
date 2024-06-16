<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\User\UserEditRoleDTO;
use App\DTO\User\UserRegisterDTO;
use App\Service\UserService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

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
        #[MapRequestPayload] UserEditRoleDTO $editRoleDTO
    ): JsonResponse {
        try {
            $this->userService->editRole($editRoleDTO);
            return new JsonResponse(['message' => 'User data saved successfully'], Response::HTTP_OK);
        } catch (Exception $e) {
            return $this->json($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}
