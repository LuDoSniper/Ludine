<?php

namespace App\Entity\Food\Stock;

use App\Repository\Food\Stock\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, StockedProduct>
     */
    #[ORM\OneToMany(targetEntity: StockedProduct::class, mappedBy: 'product')]
    private Collection $stockedProducts;

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
            $stockedProduct->setProduct($this);
        }

        return $this;
    }

    public function removeStockedProduct(StockedProduct $stockedProduct): static
    {
        if ($this->stockedProducts->removeElement($stockedProduct)) {
            // set the owning side to null (unless already changed)
            if ($stockedProduct->getProduct() === $this) {
                $stockedProduct->setProduct(null);
            }
        }

        return $this;
    }
}
