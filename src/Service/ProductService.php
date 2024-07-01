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
        private EntityManagerInterface $em,
        private CacheInterface $cache,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function create(CreateProductDTO $createProductDTO): void
    {
        if ($createProductDTO->id) {
            $product = $this->em->find(Product::class, $createProductDTO->id);
        } else {
            $product = new Product();
        }
        $measurement = $product->getMeasurement() ?? new Measurement();
        $this->em->beginTransaction();
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
            $this->em->persist($product);
            $this->em->flush();
            $this->em->commit();
        } catch (Exception $e) {
            $this->em->rollback();
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

            $products = $this->em->getRepository(Product::class)->findAll();
            return $this->serializer->serialize($products, 'json', ['groups' => 'product:read']);
        });
    }
}
