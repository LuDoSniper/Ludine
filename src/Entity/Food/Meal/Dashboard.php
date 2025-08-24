<?php

namespace App\Entity\Food\Meal;

use App\Entity\Authentication\User;
use App\Repository\Food\Meal\DashboardRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DashboardRepository::class)]
class Dashboard
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'dashboards')]
    private ?Dish $lunchDish = null;

    #[ORM\ManyToOne(inversedBy: 'dashboards')]
    private ?Dish $lunchDishDoable = null;

    #[ORM\ManyToOne]
    private ?Dish $dinerDish = null;

    #[ORM\ManyToOne]
    private ?Dish $dinerDishDoable = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date = null;

    #[ORM\OneToOne(inversedBy: 'dashboard', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLunchDish(): ?Dish
    {
        return $this->lunchDish;
    }

    public function setLunchDish(?Dish $lunchDish): static
    {
        $this->lunchDish = $lunchDish;

        return $this;
    }

    public function getLunchDishDoable(): ?Dish
    {
        return $this->lunchDishDoable;
    }

    public function setLunchDishDoable(?Dish $lunchDishDoable): static
    {
        $this->lunchDishDoable = $lunchDishDoable;

        return $this;
    }

    public function getDinerDish(): ?Dish
    {
        return $this->dinerDish;
    }

    public function setDinerDish(?Dish $dinerDish): static
    {
        $this->dinerDish = $dinerDish;

        return $this;
    }

    public function getDinerDishDoable(): ?Dish
    {
        return $this->dinerDishDoable;
    }

    public function setDinerDishDoable(?Dish $dinerDishDoable): static
    {
        $this->dinerDishDoable = $dinerDishDoable;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }
}
