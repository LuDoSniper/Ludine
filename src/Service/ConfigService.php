<?php

namespace App\Service;

use App\Entity\Authentication\User;
use App\Entity\Food\Meal\Config;
use Doctrine\ORM\EntityManagerInterface;

class ConfigService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ){}

    public function initializeDefault(User $user): Config
    {
        $config = $this->setToDefault(new Config());
        $config->setOwner($user);

        $this->entityManager->persist($config);
        $this->entityManager->flush();

        return $config;
    }

    public function setToDefault(
        Config $config
    ): Config
    {
        $config
            ->setSelectionMode(1)
            ->setSelectLunch(true)
            ->setSelectDiner(true)
            ->setLunchTime(new \DateTime("12:00"))
            ->setDinerTime(new \DateTime("19:00"))
            ->setMaxDifficulty(5)
        ;

        return $config;
    }
}