<?php

declare(strict_types=1);

namespace App\DTO\Product\Response;

use App\Entity\Product;

final readonly class SearchProductResult
{
    public function __construct(
        public Product $product,
        public float $score,
    ) {
    }
}