<?php

namespace App\Controller\Food\Meal;

use App\Entity\Food\Meal\Config;
use App\Entity\Food\Meal\Dish;
use App\Entity\Food\Meal\Tag;
use App\Form\Food\Meal\DishType;
use App\Service\ConfigService;
use App\Service\EntityService;
use Doctrine\Common\Collections\ArrayCollection;
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
        private readonly EntityService  $entityService,
        private readonly ConfigService $configService
    ){}

    #[Route('/food/meal/dishes', 'food_meal_dishes')]
    public function dishes(): Response
    {
        $dishes = $this->entityService->getEntityRecords($this->getUser(), Dish::class, 'name');

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
        $dish->setOwner($this->getUser());

        $config = $this->entityService->getEntityRecords($this->getUser(), Config::class);
        if (count($config) >= 1) {
            $config = $config[0];
        } else {
            $config = $this->configService->initializeDefault($this->getUser());
        }

        $form = $this->createForm(DishType::class, $dish, [
            'maxDifficulty' => $config->getMaxDifficulty(),
            'user' => $this->getUser(),
            'tags' => $this->entityService->getEntityRecords($this->getUser(), Tag::class, 'name')
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
                            'string' => 'Quantité',
                            'sequence' => 2,
                        ],
                        [
                            'name' => 'product',
                            'type' => 'relational',
                            'string' => 'Produit',
                            'get_meta' => '/food/stock/products/get_meta',
                            'get_path' => '/food/stock/products/get',
                            'sequence' => 1,
                            'display' => [
                                'name',
                                'description',
                            ]
                        ],
                    ],
                    'model' => 'ingredient',
                    'save_path' => '/food/meal/ingredients/save',
                    'get_path' => '/food/meal/ingredients/get',
                ]
            ],
        ]);
    }

    #[Route('/food/meal/dish/remove/{id}', 'food_meal_dish_remove', defaults: ['id' => null])]
    public function remove(
        ?Dish $dish,
    ): Response
    {
        if (!$dish) {
            return $this->redirectToRoute('food_meal_dishes');
        }

        $this->entityManager->remove($dish);
        $this->entityManager->flush();

        return $this->redirectToRoute('food_meal_dishes');
    }

    #[Route('/food/meal/dish/update/{id}', 'food_meal_dish_update')]
    public function update(
        Dish $dish,
        Request $request
    ): Response
    {
        $config = $this->entityService->getEntityRecords($this->getUser(), Config::class);
        if (count($config) >= 1) {
            $config = $config[0];
        } else {
            $config = $this->configService->initializeDefault($this->getUser());
        }

        $form = $this->createForm(DishType::class, $dish, [
            'maxDifficulty' => $config->getMaxDifficulty(),
            'user' => $this->getUser(),
            'tags' => $this->entityService->getEntityRecords($this->getUser(), Tag::class, 'name')
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('food_meal_dishes');
        }

        return $this->render('Page/Food/Meal/dish-create.html.twig', [
            'id' => $dish->getId(),
            'form' => $form->createView(),
            'trees' => [
                'ingredients' => [
                    'fields' => [
                        [
                            'name' => 'quantity',
                            'type' => 'float',
                            'string' => 'Quantité',
                            'sequence' => 2,
                        ],
                        [
                            'name' => 'product',
                            'type' => 'relational',
                            'string' => 'Produit',
                            'get_meta' => '/food/stock/products/get_meta',
                            'get_path' => '/food/stock/products/get',
                            'sequence' => 1,
                            'display' => [
                                'name',
                                'description',
                            ]
                        ],
                    ],
                    'model' => 'ingredient',
                    'save_path' => '/food/meal/ingredients/save',
                    'get_path' => '/food/meal/ingredients/get',
                ]
            ],
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
        if (empty($data['dropRate'])) {
            $missing_fields[] = 'dropRate';
        }

        if (!empty($missing_fields)) {
            return new JsonResponse(['missing_fields' => $missing_fields], Response::HTTP_BAD_REQUEST);
        }

        if ($data['id'] === 'new') {
            $dish = new Dish();
            $dish->setOwner($this->getUser());
        } else {
            $dish = $this->entityManager->getRepository(Dish::class)->find((int) $data['id']);
        }

        $dish->setName($data['name']);
        $dish->setDescription($data['description']);
        $dish->setInstructions($data['instructions']);
        $dish->setPreparationTime($data['preparationTime']);
        $dish->setCookingTime($data['cookingTime']);
        $dish->setDifficulty($data['difficulty']);
        $tags = [];
        foreach (explode(',', $data['tags']) as $tagId) {
            $tag = $this->entityManager->getRepository(Tag::class)->find((int) $tagId);
            if ($tag) {
                $tags[] = $tag;
            }
        }
        $dish->setTags(new ArrayCollection($tags));
        if ($data['ingredients']) {
            $dish->setIngredients($data['ingredients']);
        }
        $dish->setDropRate($data['dropRate']);

        if ($data['id'] === 'new') {
            $this->entityManager->persist($dish);
        }
        $this->entityManager->flush();

        return new JsonResponse(['dish' => [
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