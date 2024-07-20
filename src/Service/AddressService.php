<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Address\CreateAddressDTO;
use App\Entity\Address;
use Doctrine\ORM\EntityManagerInterface;

final readonly class AddressService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function create(CreateAddressDTO $createAddressDTO): void
    {
        $address = Address::create($createAddressDTO->fullAddress, $createAddressDTO->kladrId);
        $this->entityManager->persist($address);
        $this->entityManager->flush();
    }
}
