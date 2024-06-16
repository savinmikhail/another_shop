<?php

declare(strict_types=1);

namespace App\DTO\Cart;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AddToCartDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('integer')]
        #[Assert\GreaterThan(0)]
        public int $productId,
    ) {
    }
}
