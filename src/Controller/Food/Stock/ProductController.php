<?php

namespace App\Controller\Food\Stock;

use App\Entity\Food\Stock\Product;
use App\Form\Food\Stock\ProductType;
use App\Service\EntityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EntityService $entityService,
    ){}

    #[Route('/food/stock/product', 'food_stock_product')]
    public function products(): Response
    {
        $products = $this->entityService->getEntityRecords($this->getUser(), Product::class, 'name');

        return $this->render('Page/Food/Stock/products.html.twig', [
            'products' => $products
        ]);
    }

    #[Route('/food/stock/product/create', 'food_stock_product_create')]
    public function create(
        Request $request
    ): Response
    {
        $product = new Product();
        $product->setOwner($this->getUser());

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($product);
            $this->entityManager->flush();

            return $this->redirectToRoute('food_stock_product');
        }

        return $this->render('Page/Food/Stock/products-create.html.twig', [
            'id' => 'new',
            'form' => $form->createView()
        ]);
    }

    #[Route('/food/stock/product/update/{id}', 'food_stock_product_update')]
    public function update(
        Product $product,
        Request $request
    ): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('food_stock_product');
        }

        return $this->render('Page/Food/Stock/products-create.html.twig', [
            'id' => $product->getId(),
            'form' => $form->createView()
        ]);
    }

    #[Route('/food/stock/product/remove/{id}', 'food_stock_product_remove', defaults: ['id' => null])]
    public function remove(
        ?Product $product
    ): Response
    {
        if (!$product) {
            return $this->redirectToRoute('food_stock_product');
        }

        $this->entityManager->remove($product);
        $this->entityManager->flush();

        return $this->redirectToRoute('food_stock_product');
    }

    #[Route('/food/stock/product/save', 'food_stock_product_save', methods: ['POST'])]
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
        if (empty($data['name'])) {
            $missing_fields[] = 'name';
        }
        if (empty($data['description'])) {
            $missing_fields[] = 'description';
        }

        if (!empty($missing_fields)) {
            return new JsonResponse(['missing_fields' => $missing_fields], Response::HTTP_BAD_REQUEST);
        }

        if ($data['id'] === 'new') {
            $product = new Product();
            $product->setOwner($this->getUser());
        } else {
            $product = $this->entityManager->getRepository(Product::class)->find((int) $data['id']);
        }

        $product->setName($data['name']);
        $product->setDescription($data['description']);

        if ($data['id'] === 'new') {
            $this->entityManager->persist($product);
        }
        $this->entityManager->flush();

        return new JsonResponse(['product' => [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
        ]]);
    }

    #[Route('/food/stock/product/get/{id}', 'food_stock_product_get', defaults: ['id' => null])]
    public function getData(
        ?int $id = null
    ): JsonResponse {
        if ($id !== null) {
            $product = $this->entityManager->getRepository(Product::class)->find($id);
            return new JsonResponse([
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
            ], Response::HTTP_OK);
        }

        $products = $this->entityService->getEntityRecords($this->getUser(), Product::class, 'name');
        $data = [];
        foreach ($products as $product) {
            $data[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
            ];
        }
        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/food/stock/product/get_meta', 'food_stock_product_get_meta')]
    public function getMeta(): JsonResponse
    {
        return new JsonResponse([
            "fields" => [
                [
                    "name" => "name",
                    "type" => "char",
                    "string" => "Nom",
                    'sequence' => 1
                ],
                [
                    "name" => "description",
                    "type" => "char",
                    "string" => "Description",
                    'sequence' => 2
                ]
            ],
            "model" => "product",
            "save_path" => '/food/stock/product/save'
        ], Response::HTTP_OK);
    }
}