<?php

namespace App\Controller\Food\Stock;

use App\Entity\Food\Stock\StockedProduct;
use App\Form\Food\Stock\StockedProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StockedProductController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ){}

    #[Route('/food/stock/stocked-products', name: 'food_stock_stocked_products')]
    public function stockedProducts(): Response
    {
        $stockedProducts = $this->entityManager->getRepository(StockedProduct::class)->findAll();

        return $this->render('Food/Stock/stocked-products.html.twig', [
            'stockedProducts' => $stockedProducts
        ]);
    }

    #[Route('/food/stock/stocked-product/create', name: 'food_stock_stocked_product_create')]
    public function create(
        Request $request
    ): Response
    {
        $stockedProduct = new StockedProduct();

        $form = $this->createForm(StockedProductType::class, $stockedProduct);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($stockedProduct);
            $this->entityManager->flush();

            return $this->redirectToRoute('food_stock_stocked_products');
        }

        return $this->render('Food/Stock/stocked-products-create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/food/stock/stocked-product/update/{id}', name: 'food_stock_stocked_product_update')]
    public function update(
        StockedProduct $stockedProduct,
        Request $request
    ): Response
    {
        $form = $this->createForm(StockedProductType::class, $stockedProduct);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('food_stock_stocked_products');
        }

        return $this->render('Food/Stock/stocked-products-update.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/food/stock/stocked-product/remove/{id}', name: 'food_stock_stocked_product_remove')]
    public function remove(
        StockedProduct $stockedProduct
    ): Response
    {
        $this->entityManager->remove($stockedProduct);
        $this->entityManager->flush();

        return $this->redirectToRoute('food_stock_stocked_products');
    }
}