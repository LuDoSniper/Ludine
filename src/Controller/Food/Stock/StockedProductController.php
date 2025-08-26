<?php

namespace App\Controller\Food\Stock;

use App\Entity\Food\Stock\Product;
use App\Entity\Food\Stock\StockedProduct;
use App\Entity\Food\Stock\Container;
use App\Form\Food\Stock\StockedProductType;
use App\Service\EntityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StockedProductController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EntityService $entityService,
    ){}

    #[Route('/food/stock/stocked-product', name: 'food_stock_stocked_product')]
    public function stockedProducts(): Response
    {
        $stockedProducts = $this->entityService->getEntityRecords($this->getUser(), StockedProduct::class);

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
        $stockedProduct->setOwner($this->getUser());

        $form = $this->createForm(StockedProductType::class, $stockedProduct, [
            'user' => $this->getUser(),
            'products' => $this->entityService->getEntityRecords($this->getUser(), Product::class),
            'containers' => $this->entityService->getEntityRecords($this->getUser(), Container::class)
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($stockedProduct);
            $this->entityManager->flush();

            return $this->redirectToRoute('food_stock_stocked_product');
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
        $form = $this->createForm(StockedProductType::class, $stockedProduct, [
            'user' => $this->getUser(),
            'products' => $this->entityService->getEntityRecords($this->getUser(), Product::class),
            'containers' => $this->entityService->getEntityRecords($this->getUser(), Container::class)
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('food_stock_stocked_product');
        }

        return $this->render('Page/Food/Stock/stocked-products-create.html.twig', [
            'id' => $stockedProduct->getId(),
            'form' => $form->createView()
        ]);
    }

    #[Route('/food/stock/stocked-product/remove/{id}', name: 'food_stock_stocked_product_remove', defaults: ['id' => null])]
    public function remove(
        ?StockedProduct $stockedProduct
    ): Response
    {
        if (!$stockedProduct) {
            return $this->redirectToRoute('food_stock_stocked_product');
        }

        $this->entityManager->remove($stockedProduct);
        $this->entityManager->flush();

        return $this->redirectToRoute('food_stock_stocked_product');
    }

    #[Route('/food/stock/stocked-product/save', 'food_stock_stocked_product_save', methods: ['POST'])]
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
            $stocked_product->setOwner($this->getUser());
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

    #[Route('/food/stock/stocked-product/get_meta', 'food_stock_stocked_product_get_meta')]
    public function getMeta(): JsonResponse
    {
        return new JsonResponse([
            "fields" => [
                [
                    "name" => "product",
                    "type" => "relational",
                    "string" => "Produit",
                    'get_meta' => '/food/stock/products/get_meta',
                    'sequence' => 1
                ],
                [
                    "name" => "arrivalDate",
                    "type" => "date",
                    "string" => "Date d'arrivée",
                    'sequence' => 2
                ],
                [
                    "name" => "expirationDate",
                    "type" => "date",
                    "string" => "Date de péremption",
                    'sequence' => 2
                ],
                [
                    "name" => "stackable",
                    "type" => "boolean",
                    "string" => "Stackable",
                    'sequence' => 2
                ],
                [
                    "name" => "cool",
                    "type" => "boolean",
                    "string" => "Frais",
                    'sequence' => 2
                ],
                [
                    "name" => "container",
                    "type" => "relational",
                    "string" => "Conteneur",
                    'get_meta' => '/food/stock/container/get_meta',
                    'sequence' => 2
                ],
                [
                    "name" => "floor",
                    "type" => "integer",
                    "string" => "Etage",
                    'sequence' => 2
                ],
                [
                    "name" => "location",
                    "type" => "integer",
                    "string" => "Emplacement",
                    'sequence' => 2
                ]
            ],
            "model" => "product",
            "save_path" => '/food/stock/products/save'
        ], Response::HTTP_OK);
    }
}