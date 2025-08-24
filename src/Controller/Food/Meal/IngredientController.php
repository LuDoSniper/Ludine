<?php

namespace App\Controller\Food\Meal;

use App\Entity\Food\Meal\Dish;
use App\Entity\Food\Meal\Ingredient;
use App\Entity\Food\Stock\Product;
use App\Service\EntityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;

class IngredientController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EntityService $entityService,
    ){}

    #[Route('/food/meal/ingredient/remove/{id}', 'food_meal_ingredient_remove')]
    public function remove(
        Ingredient $ingredient,
        Request $request,
        RouterInterface $router,
    ): Response
    {
        $this->entityManager->remove($ingredient);
        $this->entityManager->flush();

        $redirect = (string) $request->query->get('redirect', 'food_meal_dish');
        $params = $request->query->all('redirect_params') ?? [];

        // sécurité: s’assurer que la route existe (anti open-redirect/typo)
        if (!$router->getRouteCollection()->get($redirect)) {
            $redirect = 'food_meal_dish';
            $params = [];
        }

        return $this->redirectToRoute($redirect, $params);
    }

    #[Route('/food/meal/ingredient/save', 'food_meal_ingredient_save')]
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
        if (empty($data['quantity'])) {
            $missing_fields[] = 'quantity';
        }
        if (empty($data['product'])) {
            $missing_fields[] = 'product';
        } else {
            $product = $this->entityManager->getRepository(Product::class)->find($data['product']);
            if (empty($product)) {
                $missing_fields[] = 'product';
            }
        }
        if (empty($data['dish_id'])) {
            $missing_fields[] = 'dish_id';
        } else {
            $dish = $this->entityManager->getRepository(Dish::class)->find($data['dish_id']);
            if (empty($dish)) {
                $missing_fields[] = 'dish_id';
            }
        }

        if (!empty($missing_fields)) {
            return new JsonResponse(['missing_fields' => $missing_fields], Response::HTTP_BAD_REQUEST);
        }

        if ($data['id'] === 'new') {
            $ingredient = new Ingredient();
            $ingredient->setOwner($this->getUser());
        } else {
            $ingredient = $this->entityManager->getRepository(Ingredient::class)->find((int) $data['id']);
        }

        $ingredient->setDish($dish);
        $ingredient->setQuantity($data['quantity']);
        $ingredient->setProduct($this->entityManager->getRepository(Product::class)->find($data['product']));

        if ($data['id'] === 'new') {
            $this->entityManager->persist($ingredient);
        }
        $this->entityManager->flush();

        return new JsonResponse(['ingredient' => [
            'id' => $ingredient->getId(),
            'quantity' => $ingredient->getQuantity(),
            'product' => $ingredient->getProduct(),
        ]]);
    }

    #[Route('/food/meal/ingredient/get/{id}', 'food_meal_ingredient_get', defaults: ['id' => null])]
    public function get(
        string | int | null $id,
    ): JSONResponse
    {
        if ($id === null || $id === 'new') return new JsonResponse(["data" => []], Response::HTTP_OK);

        $dish = $this->entityManager->getRepository(Dish::class)->find($id);
        if (!$dish) return new JsonResponse(["data" => []], Response::HTTP_OK);

//        $ingredients = $this->entityService->getEntityRecords($this->getUser(), Ingredient::class);
        $ingredients = $dish->getIngredients();

        $data = ["data" => []];
        foreach ($ingredients as $ingredient) {
            $data["data"][] = [
                'id' => $ingredient->getId(),
                'quantity' => $ingredient->getQuantity(),
                'product' => [
                    'id' => $ingredient->getProduct()->getId(),
                    'name' => $ingredient->getProduct()->getName(),
                    'description' => $ingredient->getProduct()->getDescription(),
                ],
            ];
        }
        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/food/meal/ingredient/get_meta', 'food_meal_ingredient_get_meta')]
    public function getMeta(): JsonResponse
    {
        return new JsonResponse([
            "fields" => [
                [
                    "name" => "product",
                    "type" => "relational",
                    "string" => "Produit",
                    'get_meta' => '/food/stock/product/get_meta',
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
            ],
            "model" => "ingredients",
            "save_path" => '/food/meal/ingredient/save'
        ], Response::HTTP_OK);
    }
}