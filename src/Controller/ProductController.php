<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Product\CreateProductDTO;
use App\Entity\Product;
use App\Service\ProductService;
use Exception;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductService $productService,
    ) {
    }

    #[Route('/api/product', name: 'add_product', methods: ['POST'])]
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
    public function index(
        EntityManagerInterface $em,
        SerializerInterface $serializer
    ): JsonResponse {
        $products = $em->getRepository(Product::class)->findAll();
        $jsonProducts = $serializer->serialize($products, 'json', ['groups' => 'product:read']);
        return new JsonResponse($jsonProducts, Response::HTTP_OK, [], true);
    }
}
