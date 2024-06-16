<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Cart\AddToCartDTO;
use App\Entity\User;
use App\Service\CartService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

final class CartController extends AbstractController
{
    public function __construct(
        private readonly CartService $cartService,
    ) {
    }

    #[Route('/api/cart', name: 'add_item', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] AddToCartDTO $addToCartDTO
    ): JsonResponse {
        try {
            /** @var User $user */
            $user = $this->getUser();
            $this->cartService->add($addToCartDTO, $user);
            return $this->json('added to cart successfully', Response::HTTP_CREATED);
        } catch (Exception $e) {
            return $this->json($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}
