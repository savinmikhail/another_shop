<?php

declare(strict_types=1);

namespace App\DTO\User;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UserRegisterDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        public string $email,
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        public string $phone,
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        public string $password,
        #[Assert\NotBlank]
        #[Assert\Type('string')]
        public string $name,
    ) {
    }
}
