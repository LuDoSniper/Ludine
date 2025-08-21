<?php

namespace App\Controller\Food\Meal;

use App\Entity\Food\Meal\Config;
use App\Entity\Food\Meal\Dashboard;
use App\Entity\Food\Meal\Dish;
use App\Service\EntityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EntityService  $entityService,
    ){}

    #[Route('/food/meal/dashboard', name: 'food_meal_dashboard')]
    public function dashboard(): Response
    {
        $dashboard = $this->entityService->getEntityRecords($this->getUser(), Dashboard::class);
        if (count($dashboard) >= 1) {
            $dashboard = $dashboard[0];
        }

        $config = $this->entityService->getEntityRecords($this->getUser(), Config::class);
        if ($config == null) {
            $config = (new ConfigController($this->entityManager))->initializeDefault();
        }

        $tz    = new \DateTimeZone('Europe/Paris');
        $today = new \DateTimeImmutable('today', $tz); // 00:00:00 aujourd'hui à Paris

        if (
            !$dashboard
            || \DateTimeImmutable::createFromInterface($dashboard->getDate())
                ->setTimezone($tz)      // passe en Europe/Paris
                ->setTime(0, 0, 0)      // normalise à minuit => on ignore l'heure
            < $today
        ) {
            $dashboard = new Dashboard();
            $dashboard->setOwner($this->getUser());
            $dashboard->setDate(new \DateTime('today', $tz));

            $factor = $config->isSelectLunch() + $config->isSelectDiner();
            $n = in_array($config->getSelectionMode(), [1, 2], true) ? 1 : 2;

            $dishes = $this->entityService->getEntityRecords($this->getUser(), Dish::class);
            $selected = $this->pickDishesWeighted($dishes, $n * $factor);

            $dashboard->setLunchDish($selected[0]);
            $dashboard->setLunchDishDoable($selected[1]);
            $dashboard->setDinerDish($selected[2]);
            $dashboard->setDinerDishDoable($selected[3]);

            $this->entityManager->persist($dashboard);
            $this->entityManager->flush();
        }

        return $this->render('Page/Food/Meal/dashboard.html.twig', [
            'config' => $config,
            'dashboard' => $dashboard,
        ]);
    }

    public function pickDishesWeighted(array $dishes, int $count, bool $unique = false): array
    {
        // Prépare le pool et les poids clampés 0..100
        $pool = [];
        $weights = [];
        foreach ($dishes as $i => $dish) {
            $pool[$i] = $dish;
            $w = (int) round($dish->getDropRate());
            if ($w < 0)   { $w = 0; }
            if ($w > 100) { $w = 100; }
            $weights[$i] = $w;
        }

        $selected = [];

        for ($n = 0; $n < $count; $n++) {
            if (empty($pool)) { break; }

            $sum = array_sum($weights);

            if ($sum > 0) {
                // Tirage pondéré : plus le dropRate est grand, plus c'est probable
                $r = random_int(1, $sum); // entier sécurisé dans [1, $sum]
                $acc = 0;
                foreach ($weights as $key => $w) {
                    $acc += $w;
                    if ($r <= $acc) {
                        $selected[] = $pool[$key];
                        if ($unique) {
                            unset($pool[$key], $weights[$key]); // sans remise
                        }
                        break;
                    }
                }
            } else {
                // Tous les poids sont 0 → tirage uniforme
                $keys = array_keys($pool);
                $key = $keys[random_int(0, count($keys) - 1)];
                $selected[] = $pool[$key];
                if ($unique) {
                    unset($pool[$key], $weights[$key]);
                }
            }
        }

        return $selected;
    }
}