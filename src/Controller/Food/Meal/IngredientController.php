<?php

namespace App\Controller\Food\Meal;

use App\Entity\Food\Meal\Ingredient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IngredientController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ){}

    #[Route('/food/meal/ingredients/save', 'food_meal_ingredients_save')]
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
        }

        if (!empty($missing_fields)) {
            return new JsonResponse(['missing_fields' => $missing_fields], Response::HTTP_BAD_REQUEST);
        }

        if ($data['id'] === 'new') {
            $ingredient = new Ingredient();
        } else {
            $ingredient = $this->entityManager->getRepository(Ingredient::class)->find((int) $data['id']);
        }

        $ingredient->setQuantity($data['quantity']);
        $ingredient->setProduct($data['product']);

        if ($data['id'] === 'new') {
            $this->entityManager->persist($ingredient);
        }
        $this->entityManager->flush();

        return new JsonResponse(['container' => [
            'id' => $ingredient->getId(),
            'quantity' => $ingredient->getQuantity(),
            'product' => $ingredient->getProduct(),
        ]]);
    }

    #[Route('/food/meal/ingredients/get', 'food_meal_ingredient_get')]
    public function get(): JSONResponse
    {
        $ingredients = $this->entityManager->getRepository(Ingredient::class)->findAll();

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
}