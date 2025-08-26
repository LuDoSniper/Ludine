<?php

namespace App\Controller\Food\Meal;

use App\Entity\Food\Meal\Tag;
use App\Service\EntityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TagController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EntityService $entityService
    ){}

    #[Route('/food/meal/tag', 'food_meal_tag')]
    public function tags(): Response
    {
        $tags = $this->entityService->getEntityRecords($this->getUser(), Tag::class, 'name');

        return $this->render('Page/Food/Meal/tags.html.twig', [
            'tags' => $tags
        ]);
    }

    #[Route('/food/meal/tag/remove/{id}', 'food_meal_tag_remove')]
    public function remove(
        Tag $tag
    ): Response
    {
        $this->entityManager->remove($tag);
        $this->entityManager->flush();

        return $this->redirectToRoute('food_meal_tag');
    }

    #[Route('/food/meal/tag/save', 'food_meal_tag_save', methods: ['POST'])]
    public function save(
        Request $request
    ): JsonResponse
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
        if (empty($data['color'])) {
            $missing_fields[] = 'color';
        }

        if (!empty($missing_fields)) {
            return new JsonResponse(['missing_fields' => $missing_fields], Response::HTTP_BAD_REQUEST);
        }

        if ($data['id'] === 'new') {
            $tag = new Tag();
            $tag->setOwner($this->getUser());
        } else {
            $tag = $this->entityManager->getRepository(Tag::class)->find((int) $data['id']);
        }

        $tag->setName($data['name']);
        $tag->setDescription($data['description']);
        $tag->setColor($data['color']);

        if ($data['id'] === 'new') {
            $this->entityManager->persist($tag);
        }
        $this->entityManager->flush();

        return new JsonResponse(['tag' => [
            'id' => $tag->getId(),
            'name' => $tag->getName(),
            'description' => $tag->getDescription(),
            'color' => $tag->getColor(),
        ]]);
    }
}