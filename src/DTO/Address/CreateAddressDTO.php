<?php

declare(strict_types=1);

namespace App\DTO\Address;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateAddressDTO
{
    public function __construct(
        #[Assert\Type('string')]
        public ?string $fullAddress,
        #[Assert\Type('integer')]
        #[Assert\GreaterThan(0)]
        public ?int $kladrId,
    ) {
    }
}
