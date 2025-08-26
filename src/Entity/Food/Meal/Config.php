<?php

namespace App\Entity\Food\Meal;

use App\Entity\Authentication\User;
use App\Repository\Food\Meal\ConfigRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConfigRepository::class)]
class Config
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $selectionMode = null;

    #[ORM\Column]
    private ?bool $selectLunch = true;

    #[ORM\Column]
    private ?bool $selectDiner = true;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lunchTime = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dinerTime = null;

    #[ORM\Column]
    private ?int $maxDifficulty = null;

    #[ORM\OneToOne(inversedBy: 'config', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSelectionMode(): ?int
    {
        return $this->selectionMode;
    }

    public function setSelectionMode(int $selectionMode): static
    {
        $this->selectionMode = $selectionMode;

        return $this;
    }

    public function isSelectLunch(): ?bool
    {
        return $this->selectLunch;
    }

    public function setSelectLunch(bool $selectLunch): static
    {
        $this->selectLunch = $selectLunch;

        return $this;
    }

    public function isSelectDiner(): ?bool
    {
        return $this->selectDiner;
    }

    public function setSelectDiner(bool $selectDiner): static
    {
        $this->selectDiner = $selectDiner;

        return $this;
    }

    public function getLunchTime(): ?\DateTimeInterface
    {
        return $this->lunchTime;
    }

    public function setLunchTime(\DateTimeInterface $lunchTime): static
    {
        $this->lunchTime = $lunchTime;

        return $this;
    }

    public function getDinerTime(): ?\DateTimeInterface
    {
        return $this->dinerTime;
    }

    public function setDinerTime(?\DateTimeInterface $dinerTime): static
    {
        $this->dinerTime = $dinerTime;

        return $this;
    }

    public function getMaxDifficulty(): ?int
    {
        return $this->maxDifficulty;
    }

    public function setMaxDifficulty(int $maxDifficulty): static
    {
        $this->maxDifficulty = $maxDifficulty;

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
