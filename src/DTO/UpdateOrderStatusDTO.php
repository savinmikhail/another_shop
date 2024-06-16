<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateOrderStatusDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Order status cannot be blank.')]
        #[Assert\Type('string')]
        public string $status,
        #[Assert\NotBlank]
        #[Assert\Type('integer')]
        #[Assert\GreaterThan(0)]
        public int $id,
    ) {
    }
}
