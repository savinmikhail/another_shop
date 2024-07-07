<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\MeasurementRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\MaxDepth;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: MeasurementRepository::class)]
class Measurement
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "SEQUENCE")]
    #[ORM\Column]
    #[Groups(['product:read'])]
    private ?int $id = null;

    #[ORM\OneToOne(mappedBy: 'measurement', cascade: ['persist', 'remove'])]
    private ?Product $product = null;

    #[ORM\Column]
    #[Groups(['product:read'])]
    private ?int $weight = null;

    #[ORM\Column]
    #[Groups(['product:read'])]
    private ?int $height = null;

    #[ORM\Column]
    #[Groups(['product:read'])]
    private ?int $width = null;

    #[ORM\Column]
    #[Groups(['product:read'])]
    private ?int $length = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): static
    {
        // set the owning side of the relation if necessary
        if ($product->getMeasurements() !== $this) {
            $product->setMeasurements($this);
        }

        $this->product = $product;

        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(int $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(int $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(int $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function setLength(int $length): static
    {
        $this->length = $length;

        return $this;
    }
}
