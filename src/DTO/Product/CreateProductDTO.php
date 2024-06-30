<?php

declare(strict_types=1);

namespace App\DTO\Product;

use App\DTO\Measurement\MeasurementDTO;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateProductDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        public string $name,
        #[Assert\NotBlank]
        #[Assert\Valid]
        public MeasurementDTO $measurements,
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        public string $description,
        #[Assert\NotBlank]
        #[Assert\Type('integer')]
        #[Assert\GreaterThan(0)]
        public int $cost,
        #[Assert\NotBlank]
        #[Assert\Type('integer')]
        #[Assert\GreaterThan(0)]
        public int $tax,
        #[Assert\NotBlank]
        #[Assert\Type('integer')]
        #[Assert\GreaterThan(0)]
        public int $version,
        #[Assert\Type('integer')]
        #[Assert\GreaterThan(0)]
        public ?int $id = null,
    ) {
    }
}
