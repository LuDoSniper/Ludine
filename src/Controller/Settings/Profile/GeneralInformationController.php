<?php

namespace App\Controller\Settings\Profile;

use App\Form\Settings\Profile\GeneralType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GeneralInformationController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ){}

    #[Route('/settings/profile/general', name: 'settings_profile_general')]
    public function generalInformation(
        Request $request,
    ): Response
    {
        $form = $this->createForm(GeneralType::class, $this->getUser());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
        }

        return $this->render('Page/Settings/Profile/general.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/settings/profile/general/save', name: 'settings_profile_general_save', methods: ['POST'])]
    public function save(
        Request $request,
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data)) {
            return new JsonResponse(['error' => 'empty data'], Response::HTTP_BAD_REQUEST);
        }

        $missing_fields = [];
        if (empty($data['email'])) {
            $missing_fields[] = 'email';
        }
        if (empty($data['username'])) {
            $missing_fields[] = 'username';
        }

        if (!empty($missing_fields)) {
            return new JsonResponse(['missing_fields' => $missing_fields], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->getUser();
        $user->setEmail($data['email']);
        $user->setUsername($data['username']);
        $user->setDisplayName($data['displayName'] ?: null);

        $this->entityManager->flush();

        return new JsonResponse(['general' => [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
            'displayName' => $user->getDisplayName(),
        ]]);
    }
}