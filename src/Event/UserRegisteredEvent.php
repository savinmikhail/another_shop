<?php

declare(strict_types=1);

namespace App\Event;


use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

final class UserRegisteredEvent  extends Event
{
    public const NAME = 'user.registered';

    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}