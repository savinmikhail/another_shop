<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Product\Request\CreateProductDTO;
use App\DTO\Product\Request\FindProductRequest;
use App\DTO\Product\Response\SearchProductResult;
use App\Entity\Measurement;
use App\Entity\Product;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Exception;
use FOS\ElasticaBundle\Finder\FinderInterface;
use FOS\ElasticaBundle\HybridResult;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

use function array_map;

final readonly class ProductService
{
    private const CACHE_KEY = 'products_list';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private CacheInterface $cache,
        private SerializerInterface $serializer,
        private FinderInterface $finder,
    ) {
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     * @throws Exception|InvalidArgumentException
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
            $this->invalidateCache();
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
        return $this->cache->get(self::CACHE_KEY, function (ItemInterface $item): string {
            $item->expiresAt((new DateTime())->modify('+1 day'));

            $products = $this->entityManager->getRepository(Product::class)->findAll();
            return $this->serializer->serialize($products, 'json');
        });
    }

    /**
     * Invalidate the product list cache.
     * @throws InvalidArgumentException
     */
    private function invalidateCache(): void
    {
        $this->cache->delete(self::CACHE_KEY);
    }

    public function search(FindProductRequest $dto): string
    {
        $res = array_map(
            static fn (HybridResult $result): SearchProductResult => new SearchProductResult(
                $result->getTransformed(),
                $result->getResult()->getScore()
            ),
            $this->finder->findHybrid($dto->search . '~2')
        );
        return $this->serializer->serialize($res, 'json');
    }
}
