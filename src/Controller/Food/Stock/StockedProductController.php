<?php

namespace App\Controller\Food\Stock;

use App\Entity\Food\Stock\Product;
use App\Entity\Food\Stock\StockedProduct;
use App\Entity\Food\Stock\Container;
use App\Form\Food\Stock\StockedProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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

        return $this->render('Page/Food/Stock/stocked-products.html.twig', [
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

        return $this->render('Page/Food/Stock/stocked-products-create.html.twig', [
            'id' => 'new',
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

        return $this->render('Page/Food/Stock/stocked-products-create.html.twig', [
            'id' => $stockedProduct->getId(),
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

    #[Route('/food/stock/stocked-products/save', 'food_stock_stocked_products_save', methods: ['POST'])]
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
        if (empty($data['product'])) {
            $missing_fields[] = 'product';
        }
        if (empty($data['arrivalDate'])) {
            $missing_fields[] = 'arrivalDate';
        }
        if (empty($data['expirationDate'])) {
            $missing_fields[] = 'expirationDate';
        }
        if (empty($data['stackable'])) {
            $missing_fields[] = 'stackable';
        }
        if (empty($data['cool'])) {
            $missing_fields[] = 'cool';
        }
        if (empty($data['container'])) {
            $missing_fields[] = 'container';
        }
        if (empty($data['floor'])) {
            $missing_fields[] = 'floor';
        }
        if (empty($data['location'])) {
            $missing_fields[] = 'location';
        }

        if (!empty($missing_fields)) {
            return new JsonResponse(['missing_fields' => $missing_fields], Response::HTTP_BAD_REQUEST);
        }

        $arrivalDate = \DateTime::createFromFormat('d/m/Y', $data['arrivalDate']);
        $expirationDate = \DateTime::createFromFormat('d/m/Y', $data['expirationDate']);
        if (!$arrivalDate || !$expirationDate) {
            return new JsonResponse(['error' => 'invalid date format'], Response::HTTP_BAD_REQUEST);
        }

        if ($data['id'] === 'new') {
            $stocked_product = new StockedProduct();
        } else {
            $stocked_product = $this->entityManager->getRepository(StockedProduct::class)->find((int) $data['id']);
        }

        $stocked_product->setProduct($this->entityManager->getRepository(Product::class)->find((int) $data['product']));
        $stocked_product->setArrivalDate($arrivalDate);
        $stocked_product->setExpirationDate($expirationDate);
        $stocked_product->setStackable($data['stackable']);
        $stocked_product->setCool($data['cool']);
        $stocked_product->setContainer($this->entityManager->getRepository(Container::class)->find((int) $data['container']));
        $stocked_product->setFloor($data['floor']);
        $stocked_product->setLocation($data['location']);

        if ($data['id'] === 'new') {
            $this->entityManager->persist($stocked_product);
        }
        $this->entityManager->flush();

        return new JsonResponse(['stocked_product' => [
            'id' => $stocked_product->getId(),
            'product' => [
                'id' => $stocked_product->getProduct()->getId(),
                'name' => $stocked_product->getProduct()->getName(),
                'description' => $stocked_product->getProduct()->getDescription(),
            ],
            'arrivalDate' => $stocked_product->getArrivalDate(),
            'expirationDate' => $stocked_product->getExpirationDate(),
            'stackable' => $stocked_product->isStackable(),
            'cool' => $stocked_product->isCool(),
            'container' => [
                'id' => $stocked_product->getContainer()->getId(),
                'name' => $stocked_product->getContainer()->getName(),
                'description' => $stocked_product->getContainer()->getDescription(),
                'cool' => $stocked_product->getContainer()->isCool(),
                'nbFloor' => $stocked_product->getContainer()->getNbFloor(),
                'ref' => $stocked_product->getContainer()->getRef(),
                'floors' => $stocked_product->getContainer()->getFloors(),
            ],
            'floor' => $stocked_product->getFloor(),
            'location' => $stocked_product->getLocation(),
        ]]);
    }

    #[Route('/food/stock/stocked-product/get/{id}', 'food_stock_stocked_product_get')]
    public function getData(
        StockedProduct $stockedProduct,
    ): JsonResponse {
        return new JsonResponse([
            'id' => $stockedProduct->getId(),
            'product' => [
                'id' => $stockedProduct->getProduct()->getId(),
                'name' => $stockedProduct->getProduct()->getName(),
                'description' => $stockedProduct->getProduct()->getDescription()
            ],
            'arrivalDate' => $stockedProduct->getArrivalDate(),
            'expirationDate' => $stockedProduct->getExpirationDate(),
            'stackable' => $stockedProduct->isStackable(),
            'cool' => $stockedProduct->isCool(),
            'container' => [
                'id' => $stockedProduct->getContainer()->getId(),
                'name' => $stockedProduct->getContainer()->getName(),
                'description' => $stockedProduct->getContainer()->getDescription(),
                'cool' => $stockedProduct->getContainer()->isCool(),
                'nbFloor' => $stockedProduct->getContainer()->getNbFloor(),
                'ref' => $stockedProduct->getContainer()->getRef(),
                'floors' => $stockedProduct->getContainer()->getFloors(),
            ],
            'floor' => $stockedProduct->getFloor(),
            'location' => $stockedProduct->getLocation(),
        ], Response::HTTP_OK);
    }
}