<?php

namespace App\Controller\Food\Meal;

use App\Entity\Food\Meal\Config;
use App\Entity\Food\Meal\Dish;
use App\Form\Food\Meal\DishType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DishController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ){}

    #[Route('/food/meal/dishes', 'food_meal_dishes')]
    public function dishes(): Response
    {
        $dishes = $this->entityManager->getRepository(Dish::class)->findAll();

        return $this->render('Page/Food/Meal/dishes.html.twig', [
            'dishes' => $dishes,
        ]);
    }

    #[Route('/food/meal/dishes/create', 'food_meal_dishes_create')]
    public function create(
        Request $request
    ): Response
    {
        $dish = new Dish();

        $config = $this->entityManager->getRepository(Config::class)->findAll();
        if (count($config) >= 1) {
            $config = $config[0];
        }

        $form = $this->createForm(DishType::class, $dish, [
            'maxDifficulty' => $config->getMaxDifficulty()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($dish);
            $this->entityManager->flush();

            return $this->redirectToRoute('food_meal_dishes');
        }

        return $this->render('Page/Food/Meal/dish-create.html.twig', [
            'id' => 'new',
            'form' => $form->createView(),
            'trees' => [
                'ingredients' => [
                    'fields' => [
                        [
                            'name' => 'quantity',
                            'type' => 'float',
                            'string' => 'Quantité'
                        ],
                        [
                            'name' => 'product',
                            'type' => 'relational',
                            'string' => 'Produit'
                        ],
                    ],
                    'model' => 'ingredient',
                    'save_path' => '/food/meal/ingredients/save',
                ]
            ],
        ]);
    }

    #[Route('/food/meal/dish/update/{id}', 'food_meal_dish_update')]
    public function update(
        Dish $dish,
        Request $request
    ): Response
    {
        $config = $this->entityManager->getRepository(Config::class)->findAll();
        if (count($config) >= 1) {
            $config = $config[0];
        }

        $form = $this->createForm(Dish::class, $dish, [
            'maxDifficulty' => $config->getMaxDifficulty()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('food_meal_dishes');
        }

        return $this->render('Page/Food/Meal/dish-create.html.twig', [
            'id' => $dish->getId(),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/food/meal/dishes/save', 'food_meal_dishes_save', methods: ['POST'])]
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
        if (empty($data['instructions'])) {
            $missing_fields[] = 'instructions';
        }
        if (empty($data['preparationTime'])) {
            $missing_fields[] = 'preparationTime';
        }
        if (empty($data['cookingTime'])) {
            $missing_fields[] = 'cookingTime';
        }
        if (empty($data['difficulty'])) {
            $missing_fields[] = 'difficulty';
        }
        if (empty($data['tags'])) {
            $missing_fields[] = 'tags';
        }
        if (empty($data['ingredients'])) {
            $missing_fields[] = 'ingredients';
        }
        if (empty($data['dropRate'])) {
            $missing_fields[] = 'dropRate';
        }

        if (!empty($missing_fields)) {
            return new JsonResponse(['missing_fields' => $missing_fields], Response::HTTP_BAD_REQUEST);
        }

        if ($data['id'] === 'new') {
            $dish = new Dish();
        } else {
            $dish = $this->entityManager->getRepository(Dish::class)->find((int) $data['id']);
        }

        $dish->setName($data['name']);
        $dish->setDescription($data['description']);
        $dish->setInstructions($data['instructions']);
        $dish->setPreparationTime($data['preparationTime']);
        $dish->setCookingTime($data['cookingTime']);
        $dish->setDifficulty($data['difficulty']);
        $dish->setTags($data['tags']);
        $dish->setIngredients($data['ingredients']);
        $dish->setDropRate($data['dropRate']);

        if ($data['id'] === 'new') {
            $this->entityManager->persist($dish);
        }
        $this->entityManager->flush();

        return new JsonResponse(['container' => [
            'id' => $dish->getId(),
            'name' => $dish->getName(),
            'description' => $dish->getDescription(),
            'instructions' => $dish->getInstructions(),
            'preparationTime' => $dish->getPreparationTime(),
            'cookingTime' => $dish->getCookingTime(),
            'difficulty' => $dish->getDifficulty(),
            'tags' => $dish->getTags(),
            'ingredients' => $dish->getIngredients(),
            'dropRate' => $dish->getDropRate(),
        ]]);
    }
}