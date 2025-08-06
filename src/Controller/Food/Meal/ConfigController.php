<?php

namespace App\Controller\Food\Meal;

use App\Entity\Food\Meal\Config;
use App\Form\Food\Meal\ConfigType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ConfigController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ){}

    #[Route('/food/meal/config', 'food_meal_config')]
    public function config(
        Request $request
    ): Response
    {
        $configs = $this->entityManager->getRepository(Config::class)->findAll();
        if (count($configs) >= 1) {
            $config = $configs[0];
        } else {
            $config = $this->setToDefault(new Config());
        }

        $form = $this->createForm(ConfigType::class, $config);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($config);
            $this->entityManager->flush();
        }

        return $this->render('Page/Food/Meal/config.html.twig', [
            'id' => $config->getId() ?: 'new',
            'form' => $form->createView()
        ]);
    }

    #[Route('/food/meal/config/save', 'food_meal_config_save', methods: ['POST'])]
    public function save(
        Request $request
    ): JSONResponse
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data)) {
            return new JsonResponse(['error' => 'empty data'], Response::HTTP_BAD_REQUEST);
        }
        if (empty($data['id'])) {
            return new JsonResponse(['missing_id' => 'missing id'], Response::HTTP_BAD_REQUEST);
        }

        $missing_fields = [];
        if (empty($data['selectionMode'])) {
            $missing_fields[] = 'selectionMode';
        }
        if (empty($data['selectLunch'])) {
            $missing_fields[] = 'selectLunch';
        }
        if (empty($data['selectDiner'])) {
            $missing_fields[] = 'selectDiner';
        }
        if (empty($data['lunchTime'])) {
            $missing_fields[] = 'lunchTime';
        }
        if (empty($data['dinerTime'])) {
            $missing_fields[] = 'dinerTime';
        }
        if (empty($data['maxDifficulty'])) {
            $missing_fields[] = 'maxDifficulty';
        }

        if (!empty($missing_fields)) {
            return new JsonResponse(['missing_fields' => $missing_fields], Response::HTTP_BAD_REQUEST);
        }

        if ($data['id'] === 'new') {
            $config = $this->setToDefault(new Config());
        } else {
            $config = $this->entityManager->getRepository(Config::class)->find((int) $data['id']);
        }

        $config->setSelectionMode($data['selectionMode']);
        $config->setSelectLunch($data['selectLunch']);
        $config->setSelectDiner($data['selectDiner']);
        $config->setLunchTime(new \DateTime($data['lunchTime']));
        $config->setDinerTime(new \DateTime($data['dinerTime']));
        $config->setMaxDifficulty($data['maxDifficulty']);

        if ($data['id'] === 'new') {
            $this->entityManager->persist($config);
        }
        $this->entityManager->flush();

        return new JsonResponse(['product' => [
            'id' => $config->getId(),
            'selectionMode' => $config->getSelectionMode(),
            'selectLunch' => $config->isSelectLunch(),
            'selectDiner' => $config->isSelectDiner(),
            'lunchTime' => $config->getLunchTime(),
            'dinerTime' => $config->getDinerTime(),
            'maxDifficulty' => $config->getMaxDifficulty(),
        ]]);
    }

    public function initializeDefault(): Config
    {
        $config = $this->setToDefault(new Config());

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