<?php

namespace App\Controller\Food\Stock;

use App\Entity\Food\Stock\Container;
use App\Form\Food\Stock\ContainerType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContainerController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ){}

    #[Route('/food/stock/containers', 'food_stock_containers')]
    public function containers(): Response
    {
        $containers = $this->entityManager->getRepository(Container::class)->findAll();

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

        $form = $this->createForm(ContainerType::class, $container);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($container);
            $this->entityManager->flush();

            return $this->redirectToRoute('food_stock_containers');
        }

        return $this->render('Page/Food/Stock/container-create.html.twig', [
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

            return $this->redirectToRoute('food_stock_containers');
        }

        return $this->render('Page/Food/Stock/container-update.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/food/stock/container/remove/{id}', 'food_stock_container_remove')]
    public function remove(
        Container $container
    ): Response
    {
        $this->entityManager->remove($container);
        $this->entityManager->flush();

        return $this->redirectToRoute('food_stock_containers');
    }
}