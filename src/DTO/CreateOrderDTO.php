<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateOrderDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Delivery type cannot be blank.')]
        #[Assert\Type('string')]
        public string $deliveryType,
        #[Assert\Optional]
        #[Assert\Type('integer')]
        #[Assert\GreaterThan(0)]
        public ?int $addressId,
    ) {
    }
}
