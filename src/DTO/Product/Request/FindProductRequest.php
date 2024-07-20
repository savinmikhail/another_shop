<?php

declare(strict_types=1);

namespace App\DTO\Product\Request;

use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Symfony\Component\Validator\Constraints as Assert;

#[Exclude]
final readonly class FindProductRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public string $search,
        #[Assert\Type('integer')]
        #[Assert\PositiveOrZero]
        public ?int $minCost = null,
        #[Assert\Type('integer')]
        #[Assert\PositiveOrZero]
        public ?int $maxCost = null,
    ) {
    }
}
