<?php

namespace App\Controller\Food\Stock;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StockController extends AbstractController
{
    #[Route('/food_stock', name: 'food_stock')]
    public function index(): Response
    {
        return $this->redirectToRoute('food_stock_containers');
    }
}