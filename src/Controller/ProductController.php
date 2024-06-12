<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Measurement;
use App\Entity\Product;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use function json_decode;

final class ProductController extends AbstractController
{
    public function __construct()
    {
    }

    #[Route('/api/product', name: 'add_product', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        if ($data['id']) {
            $product = $em->find(Product::class, $data['id']);
        } else {
            $product = new Product();
        }
        $measurement = $product->getMeasurement() ?? new Measurement();
        $measurement
            ->setWeight($data['measurements']['weight'])
            ->setHeight($data['measurements']['height'])
            ->setLength($data['measurements']['length'])
            ->setWidth($data['measurements']['width'])
            ;
        $product
            ->setName($data['name'])
            ->setDescription($data['description'])
            ->setCost($data['cost'])
            ->setTax($data['tax'])
            ->setVersion($data['version'])
            ->setMeasurement($measurement);
        $em->persist($product);
        $em->flush();

        return new JsonResponse(['product' => $product], Response::HTTP_CREATED);
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