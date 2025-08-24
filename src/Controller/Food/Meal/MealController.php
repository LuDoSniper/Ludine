<?php

namespace App\Controller\Food\Meal;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MealController extends AbstractController
{
    #[Route('/food/meal', 'food_meal')]
    public function index(): Response
    {
        return $this->redirectToRoute('food_meal_tag');
    }
}