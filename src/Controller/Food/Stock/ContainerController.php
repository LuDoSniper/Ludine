<?php

namespace App\Controller\Food\Stock;

use App\Entity\Food\Stock\Container;
use App\Form\Food\Stock\ContainerType;
use App\Service\EntityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContainerController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EntityService $entityService
    ){}

    #[Route('/food/stock/container', 'food_stock_container')]
    public function containers(): Response
    {
        $containers = $this->entityService->getEntityRecords($this->getUser(), Container::class, 'name');

        return $this->render('Page/Food/Stock/containers.html.twig', [
            'containers' => $containers
        ]);
    }

    #[Route('/food/stock/container/create', 'food_stock_container_create')]
    public function create(
        Request $request
    ): Response
    {
        $container = new Container();
        $container->setOwner($this->getUser());

        $form = $this->createForm(ContainerType::class, $container);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($container);
            $this->entityManager->flush();

            return $this->redirectToRoute('food_stock_container');
        }

        return $this->render('Page/Food/Stock/container-create.html.twig', [
            'id' => 'new',
            'form' => $form->createView()
        ]);
    }

    #[Route('/food/stock/container/update/{id}', 'food_stock_container_update')]
    public function update(
        Container $container,
        Request $request
    ): Response
    {
        $form = $this->createForm(ContainerType::class, $container);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('food_stock_container');
        }

        return $this->render('Page/Food/Stock/container-create.html.twig', [
            'id' => $container->getId(),
            'form' => $form->createView(),
            'nbFloor' => $container->getNbFloor(),
            'floors' => $container->getFloors()
        ]);
    }

    #[Route('/food/stock/container/remove/{id}', 'food_stock_container_remove', defaults: ['id' => null])]
    public function remove(
        ?Container $container
    ): Response
    {
        if (!$container) {
            return $this->redirectToRoute('food_stock_container');
        }

        $this->entityManager->remove($container);
        $this->entityManager->flush();

        return $this->redirectToRoute('food_stock_container');
    }

    #[Route('/food/stock/container/save', 'food_stock_container_save', methods: ['POST'])]
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
        if (empty($data['ref'])) {
            $missing_fields[] = 'ref';
        }
        if (empty($data['cool'])) {
            $missing_fields[] = 'cool';
        }
        if (empty($data['nbFloor'])) {
            $missing_fields[] = 'nbFloor';
        }
        if (empty($data['floors'])) {
            $missing_fields[] = 'floors';
        }

        if (!empty($missing_fields)) {
            return new JsonResponse(['missing_fields' => $missing_fields], Response::HTTP_BAD_REQUEST);
        }

        if ($data['id'] === 'new') {
            $container = new Container();
            $container->setOwner($this->getUser());
        } else {
            $container = $this->entityManager->getRepository(Container::class)->find((int) $data['id']);
        }

        $container->setName($data['name']);
        $container->setDescription($data['description']);
        $container->setRef($data['ref']);
        $container->setCool($data['cool']);
        $container->setNbFloor($data['nbFloor']);
        $container->setFloors($data['floors']);

        if ($data['id'] === 'new') {
            $this->entityManager->persist($container);
        }
        $this->entityManager->flush();

        return new JsonResponse(['container' => [
            'id' => $container->getId(),
            'name' => $container->getName(),
            'description' => $container->getDescription(),
            'ref' => $container->getRef(),
            'cool' => $container->isCool(),
            'nbFloor' => $container->getNbFloor(),
            'floors' => $container->getFloors(),
        ]]);
    }

    #[Route('/food/stock/container/get/{id}', 'food_stock_container_get')]
    public function getData(
        Container $container
    ): JsonResponse {
        return new JsonResponse([
            'id' => $container->getId(),
            'name' => $container->getName(),
            'description' => $container->getDescription(),
            'ref' => $container->getRef(),
            'cool' => $container->isCool(),
            'nbFloor' => $container->getNbFloor(),
            'floors' => $container->getFloors(),
        ], Response::HTTP_OK);
    }

    #[Route('/food/stock/container/floors/get/{id}/{floorID}', 'food_stock_container_floors_get')]
    public function getFloorData(
        Container $container,
        int $floorID
    ): JsonResponse {
        foreach ($container->getFloors() as $floor) {
            if ($floor['id'] === $floorID) {
                return new JsonResponse([
                    'id' => $floor['id'],
                    'description' => $floor['description'],
                    'locations' => $floor['locations'],
                ], Response::HTTP_OK);
            }
        }

        return new JsonResponse([
            'message' => 'floor not found',
        ], Response::HTTP_BAD_REQUEST);
    }
}