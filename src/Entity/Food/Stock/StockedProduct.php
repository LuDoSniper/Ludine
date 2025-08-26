<?php

namespace App\Entity\Food\Stock;

use App\Entity\Authentication\User;
use App\Repository\Food\Stock\StockedProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StockedProductRepository::class)]
class StockedProduct
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'stockedProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $arrivalDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $expirationDate = null;

    #[ORM\Column]
    private ?bool $stackable = false;

    #[ORM\Column]
    private ?bool $cool = false;

    #[ORM\ManyToOne(inversedBy: 'stockedProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Container $container = null;

    #[ORM\Column]
    private ?int $floor = null;

    #[ORM\Column]
    private ?int $location = null;

    #[ORM\ManyToOne(inversedBy: 'stockedProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getArrivalDate(): ?\DateTimeInterface
    {
        return $this->arrivalDate;
    }

    public function setArrivalDate(\DateTimeInterface $arrivalDate): static
    {
        $this->arrivalDate = $arrivalDate;

        return $this;
    }

    public function getExpirationDate(): ?\DateTimeInterface
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(\DateTimeInterface $expirationDate): static
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    public function isStackable(): ?bool
    {
        return $this->stackable;
    }

    public function setStackable(bool $stackable): static
    {
        $this->stackable = $stackable;

        return $this;
    }

    public function isCool(): ?bool
    {
        return $this->cool;
    }

    public function setCool(bool $cool): static
    {
        $this->cool = $cool;

        return $this;
    }

    public function getContainer(): ?Container
    {
        return $this->container;
    }

    public function setContainer(?Container $container): static
    {
        $this->container = $container;

        return $this;
    }

    public function getFloor(): ?int
    {
        return $this->floor;
    }

    public function setFloor(int $floor): static
    {
        $this->floor = $floor;

        return $this;
    }

    public function getLocation(): ?int
    {
        return $this->location;
    }

    public function setLocation(int $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }
}
