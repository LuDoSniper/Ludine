<?php

namespace App\Controller\Food\Meal;

use App\Entity\Food\Meal\Config;
use App\Entity\Food\Meal\Dashboard;
use App\Entity\Food\Meal\Dish;
use App\Service\ConfigService;
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
        private readonly ConfigService $configService
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
            $config = $this->configService->initializeDefault($this->getUser());
        }
        if (count($config) >= 1) {
            $config = $config[0];
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
            if (!$dashboard) {
                $dashboard = new Dashboard();
                $dashboard->setOwner($this->getUser());
            }
            $dashboard->setDate(new \DateTime('today', $tz));
            $dashboard->setLunchDish(null);
            $dashboard->setLunchDishDoable(null);
            $dashboard->setDinerDish(null);
            $dashboard->setDinerDishDoable(null);

            $dishes = $this->entityService->getEntityRecords($this->getUser(), Dish::class);

            if ($config->isSelectLunch() && in_array($config->getSelectionMode(), ['1', '3'])) {
                $selection = $this->pickDishesWeighted($dishes, 1);
                $dashboard->setLunchDish($selection ? $selection[0] : null);
            }
            if ($config->isSelectLunch() && in_array($config->getSelectionMode(), ['2', '3'])) {
                $selection = $this->pickDishesWeighted($dishes, 1);
                $dashboard->setLunchDishDoable($selection ? $selection[0] : null);
            }
            if ($config->isSelectDiner() && in_array($config->getSelectionMode(), ['1', '3'])) {
                $selection = $this->pickDishesWeighted($dishes, 1);
                $dashboard->setDinerDish($selection ? $selection[0] : null);
            }
            if ($config->isSelectDiner() && in_array($config->getSelectionMode(), ['2', '3'])) {
                $selection = $this->pickDishesWeighted($dishes, 1);
                $dashboard->setDinerDishDoable($selection ? $selection[0] : null);
            }

            if (
                !$dashboard->getLunchDish() &&
                !$dashboard->getLunchDishDoable() &&
                !$dashboard->getDinerDish() &&
                !$dashboard->getDinerDishDoable()
            ) {
                $dashboard = false;
            } else {
                $this->entityManager->persist($dashboard);
                $this->entityManager->flush();
            }
        }

        return $this->render('Page/Food/Meal/dashboard.html.twig', [
            'config' => $config,
            'dashboard' => $dashboard,
        ]);
    }

    #[Route('/food/meal/dashboard/recompute', 'food_meal_dashboard_recompute')]
    public function recompute(): Response
    {
        $dashboard = $this->entityService->getEntityRecords($this->getUser(), Dashboard::class);
        if (count($dashboard) >= 1) {
            $dashboard = $dashboard[0];
        }

        if ($dashboard) {
            $dashboard->setDate((new \DateTime('today', new \DateTimeZone('Europe/Paris')))->modify('-2 days'));
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('food_meal_config');
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