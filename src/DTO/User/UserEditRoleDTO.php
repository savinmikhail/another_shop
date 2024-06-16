<?php

declare(strict_types=1);

namespace App\DTO\User;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UserEditRoleDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Type('integer')]
        public int $role,
        #[Assert\NotBlank]
        #[Assert\Type('integer')]
        public int $id,
    ) {
    }
}
