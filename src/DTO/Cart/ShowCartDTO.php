<?php

declare(strict_types=1);

namespace App\DTO\Cart;

final readonly class ShowCartDTO
{
    public function __construct(public array $items, public int $total)
    {
    }
}
