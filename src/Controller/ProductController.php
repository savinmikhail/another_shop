<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Product\Request\CreateProductDTO;
use App\DTO\Product\Request\FindProductRequest;
use App\Service\ProductService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

final class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductService $productService,
    ) {
    }

    #[Route('/api/products', name: 'add_product', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] CreateProductDTO $createProductDTO,
    ): JsonResponse {
        try {
            $this->productService->create($createProductDTO);
            return new JsonResponse('product was added successfully', Response::HTTP_CREATED);
        } catch (Exception $e) {
            return $this->json($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/api/products', name: 'get_products', methods: ['GET'])]
    public function index(): JsonResponse
    {
        try {
            $response = $this->productService->index();
            return new JsonResponse($response, Response::HTTP_OK, [], true);
        } catch (Exception $e) {
            return $this->json($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/api/products/search', name: 'search_products', methods: ['GET'])]
    public function search(Request $request): JsonResponse {
        try {
            $findProductRequest = new FindProductRequest(
                search: $request->query->get('search')
            );

            $response = $this->productService->search($findProductRequest);

            return new JsonResponse($response, Response::HTTP_OK, [], true);
        } catch (Exception $e) {
            return $this->json($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}
