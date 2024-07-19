<?php

declare(strict_types=1);

namespace App\DTO\Product;

use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Symfony\Component\Validator\Constraints as Assert;

#[Exclude]
final readonly class FindProductRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public string $search,
    ) {
    }
}
