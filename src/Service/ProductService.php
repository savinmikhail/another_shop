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
use FOS\ElasticaBundle\HybridResult;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Elastica\Query;
use FOS\ElasticaBundle\Finder\HybridFinderInterface;

use function array_map;
use function count;

final readonly class ProductService
{
    private const CACHE_KEY = 'products_list';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private CacheInterface $cache,
        private HybridFinderInterface $finder,
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
    public function index(): array
    {
        return $this->cache->get(self::CACHE_KEY, function (ItemInterface $item): array {
            $item->expiresAt((new DateTime())->modify('+1 day'));

            return $this->entityManager->getRepository(Product::class)->findAll();
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

    public function search(FindProductRequest $dto): array
    {
        $boolQuery = new Query\BoolQuery();

        $range = [];
        if ($dto->minCost !== null) {
            $range['gte'] = $dto->minCost;
        }
        if ($dto->maxCost !== null) {
            $range['lte'] = $dto->maxCost;
        }
        if (count($range) > 0) {
            $boolQuery->addMust(new Query\Range('cost', $range));
        }

        $boolQuery->addShould(new Query\Fuzzy('name', $dto->search));
        $boolQuery->addShould(new Query\Fuzzy('description', $dto->search));

        return array_map(
            static fn (HybridResult $result): SearchProductResult => new SearchProductResult(
                $result->getTransformed(),
                $result->getResult()->getScore()
            ),
            $this->finder->findHybrid(new Query($boolQuery))
        );
    }
}
