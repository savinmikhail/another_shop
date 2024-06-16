<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Product\CreateProductDTO;
use App\Entity\Measurement;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ProductService
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    public function create(CreateProductDTO $createProductDTO): void
    {
        if ($createProductDTO->id) {
            $product = $this->em->find(Product::class, $createProductDTO->id);
        } else {
            $product = new Product();
        }
        $measurement = $product->getMeasurement() ?? new Measurement();
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
    }
}
