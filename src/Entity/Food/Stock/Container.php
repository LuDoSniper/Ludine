<?php

namespace App\Entity\Food\Stock;

use App\Repository\Food\Stock\ContainerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContainerRepository::class)]
class Container
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $cool = null;

    #[ORM\Column]
    private ?int $nbFloor = null;

    #[ORM\Column(length: 255)]
    private ?string $ref = null;

    /**
     * @var Collection<int, StockedProduct>
     */
    #[ORM\OneToMany(targetEntity: StockedProduct::class, mappedBy: 'container')]
    private Collection $stockedProducts;

    #[ORM\Column(type: Types::ARRAY)]
    private array $floors = [];

    public function __construct()
    {
        $this->stockedProducts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

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

    public function getNbFloor(): ?int
    {
        return $this->nbFloor;
    }

    public function setNbFloor(int $nbFloor): static
    {
        $this->nbFloor = $nbFloor;

        return $this;
    }

    public function getRef(): ?string
    {
        return $this->ref;
    }

    public function setRef(string $ref): static
    {
        $this->ref = $ref;

        return $this;
    }

    /**
     * @return Collection<int, StockedProduct>
     */
    public function getStockedProducts(): Collection
    {
        return $this->stockedProducts;
    }

    public function addStockedProduct(StockedProduct $stockedProduct): static
    {
        if (!$this->stockedProducts->contains($stockedProduct)) {
            $this->stockedProducts->add($stockedProduct);
            $stockedProduct->setContainer($this);
        }

        return $this;
    }

    public function removeStockedProduct(StockedProduct $stockedProduct): static
    {
        if ($this->stockedProducts->removeElement($stockedProduct)) {
            // set the owning side to null (unless already changed)
            if ($stockedProduct->getContainer() === $this) {
                $stockedProduct->setContainer(null);
            }
        }

        return $this;
    }

    public function getFloors(): array
    {
        return $this->floors;
    }

    public function setFloors(array $floors): static
    {
        $this->floors = $floors;

        return $this;
    }
}
