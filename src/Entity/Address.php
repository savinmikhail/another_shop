<?php

namespace App\Entity;

use App\Repository\AddressRepository;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
class Address
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "SEQUENCE")]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $kladrId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fullAddress = null;

    private function __construct(?string $fullAddress, ?int $kladrId)
    {
        $this->setFullAddress($fullAddress);
        $this->setKladrId($kladrId);
        $this->validate();
    }

    public static function create(?string $fullAddress, ?int $kladrId): self
    {
        return new self($fullAddress, $kladrId);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKladrId(): ?int
    {
        return $this->kladrId;
    }

    private function setKladrId(?int $kladrId): static
    {
        if ($kladrId !== null && $kladrId < 0) {
            throw new InvalidArgumentException('KLADR ID must be a non-negative integer or null.');
        }
        $this->kladrId = $kladrId;
        return $this;
    }

    public function getFullAddress(): ?string
    {
        return $this->fullAddress;
    }

    private function setFullAddress(?string $fullAddress): static
    {
        if ($fullAddress !== null && empty(trim($fullAddress))) {
            throw new InvalidArgumentException('Full address cannot be an empty string.');
        }
        $this->fullAddress = $fullAddress;
        return $this;
    }

    private function validate(): void
    {
        if ($this->fullAddress === null && $this->kladrId === null) {
            throw new InvalidArgumentException('At least one of fullAddress or kladrId must be set.');
        }
    }
}
