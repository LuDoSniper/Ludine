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

    #[Route('/food/stock/container', 'app_food_stock_container')]
    public function containerIndex(): Response
    {
        return new Response();
    }

    #[Route('/food/stock/container/create', 'app_food_stock_container_create')]
    public function createContainer(
        Request $request
    ): Response
    {
        $container = new Container();

        $form = $this->createForm(ContainerType::class, $container);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($container);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_food_stock_container');
        }

        return $this->render('Page/Food/Stock/Container/create.html.twig', [
            'form' => $form
        ]);
    }
}