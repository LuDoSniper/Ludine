<?php

namespace App\Controller\Food;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FoodController extends AbstractController
{
    #[Route('/food', name: 'food')]
    public function index(): Response
    {
        return $this->redirectToRoute('food_stock');
    }
}