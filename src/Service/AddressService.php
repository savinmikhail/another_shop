<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Address\CreateAddressDTO;
use App\Entity\Address;
use Doctrine\ORM\EntityManagerInterface;

final readonly class AddressService
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function create(CreateAddressDTO $createAddressDTO): void
    {
        $address = new Address();
        $address
            ->setFullAddress($createAddressDTO->fullAddress)
            ->setKladrId($createAddressDTO->kladrId);
        $this->em->persist($address);
        $this->em->flush();
    }
}
