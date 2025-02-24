<?php

namespace App\Controller\Food\Stock;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StockController extends AbstractController
{
    #[Route('/food/stock', 'app_food_stock')]
    public function stockIndex(): Response
    {
        return new Response('stock index');
    }
}