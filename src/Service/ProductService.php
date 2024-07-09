<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Product\CreateProductDTO;
use App\Entity\Measurement;
use App\Entity\Product;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final readonly class ProductService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CacheInterface $cache,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws Exception
     */
    public function create(CreateProductDTO $createProductDTO): void
    {
        $product = $createProductDTO->id
            ? $this->entityManager->find(Product::class, $createProductDTO->id)
            : new Product();
        $measurement = $product->getMeasurement() ?? new Measurement();
        $this->entityManager->beginTransaction();
        try {
            $measurement
                ->setWeight($createProductDTO->measurements->weight)
                ->setHeight($createProductDTO->measurements->height)
                ->setLength($createProductDTO->measurements->length)
                ->setWidth($createProductDTO->measurements->width);
            $product
                ->setName($createProductDTO->name)
                ->setDescription($createProductDTO->description)
                ->setCost($createProductDTO->cost)
                ->setTax($createProductDTO->tax)
                ->setVersion($createProductDTO->version)
                ->setMeasurement($measurement);
            $this->entityManager->persist($product);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function index(): string
    {
        $cacheKey = 'products_list';
        return $this->cache->get($cacheKey, function (ItemInterface $item) {
            $item->expiresAt((new DateTime())->modify('+1 day'));

            $products = $this->entityManager->getRepository(Product::class)->findAll();
            return $this->serializer->serialize($products, 'json', ['groups' => 'product:read']);
        });
    }
}
