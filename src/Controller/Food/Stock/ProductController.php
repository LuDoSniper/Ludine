<?php

namespace App\Controller\Food\Stock;

use App\Entity\Food\Stock\Product;
use App\Form\Food\Stock\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ){}

    #[Route('/food/stock/products', 'food_stock_products')]
    public function products(): Response
    {
        $products = $this->entityManager->getRepository(Product::class)->findAll();

        return $this->render('Page/Food/Stock/products.html.twig', [
            'products' => $products
        ]);
    }

    #[Route('/food/stock/products/create', 'food_stock_products_create')]
    public function create(
        Request $request
    ): Response
    {
        $product = new Product();

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($product);
            $this->entityManager->flush();

            return $this->redirectToRoute('food_stock_products');
        }

        return $this->render('Page/Food/Stock/products-create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/food/stock/products/update/{id}', 'food_stock_products_update')]
    public function update(
        Product $product,
        Request $request
    ): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('food_stock_products');
        }

        return $this->render('Page/Food/Stock/products-update.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/food/stock/products/remove/{id}', 'food_stock_products_remove')]
    public function remove(
        Product $product
    ): Response
    {
        $this->entityManager->remove($product);
        $this->entityManager->flush();

        return $this->redirectToRoute('food_stock_products');
    }
}