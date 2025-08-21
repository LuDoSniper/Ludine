<?php

namespace App\Controller\Settings\General;

use App\Entity\Authentication\User;
use App\Entity\Settings\General\Share;
use App\Form\Settings\General\ShareType;
use App\Service\EntityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ShareController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EntityService $entityService,
    ){}

    #[Route('/settings/general/shares', 'settings_general_shares')]
    public function shares(): Response
    {
        $shares = $this->entityManager->getRepository(Share::class)->findBy(['owner' => $this->getUser()]);

        return $this->render('Page/Settings/General/shares.html.twig', [
            'shares' => $shares,
        ]);
    }

    #[Route('/settings/general/share/create', 'settings_general_share_create')]
    public function create(
        Request $request
    ): Response
    {
        $share = new Share();
        $share->setOwner($this->getUser());

        $form = $this->createForm(ShareType::class, $share, [
            'entities' => $this->entityService->getEntities(),
            'selectedEntities' => $share->getEntities(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($share);
            $this->entityManager->flush();

            return $this->redirectToRoute('settings_general_shares');
        }

        return $this->render('Page/Settings/General/share-create.html.twig', [
            'id' => 'new',
            'form' => $form->createView()
        ]);
    }

    #[Route('/settings/general/share/update/{id}', 'settings_general_share_update')]
    public function update(
        Share $share,
        Request $request
    ): Response
    {
        $form = $this->createForm(ShareType::class, $share, [
            'entities' => $this->entityService->getEntities(),
            'selectedEntities' => $share->getEntities(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('settings_general_shares');
        }

        return $this->render('Page/Settings/General/share-create.html.twig', [
            'id' => $share->getId(),
            'form' => $form->createView()
        ]);
    }

    #[Route('/settings/general/share/remove/{id}', 'settings_general_share_remove')]
    public function remove(
        Share $share
    ): Response
    {
        $this->entityManager->remove($share);
        $this->entityManager->flush();

        return $this->redirectToRoute('settings_general_shares');
    }

    #[Route('/settings/general/shares/save', 'settings_general_shares_save', methods: ['POST'])]
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
        if (empty($data['entities'])) {
            $missing_fields[] = 'entities';
        }
        if (empty($data['members'])) {
            $missing_fields[] = 'members';
        }
        if (!isset($data['active'])) {
            $missing_fields[] = 'active';
        }

        if (!empty($missing_fields)) {
            return new JsonResponse(['missing_fields' => $missing_fields], Response::HTTP_BAD_REQUEST);
        }

        if ($data['id'] === 'new') {
            $share = new Share();
            $share->setOwner($this->getUser());
        } else {
            $share = $this->entityManager->getRepository(Share::class)->find((int) $data['id']);
        }

        $share->setName($data['name']);
        $share->setDescription($data['description'] ?: null);
        $share->setEntities(array_map(fn($e) => (int) $e, explode(',', $data['entities'])));
        foreach (explode(',', $data['members']) as $memberId) {
            $member = $this->entityManager->getRepository(User::class)->findOneBy(['id' => (int) $memberId]);
            if ($member) {
                $share->addMember($member);
            }
        }
        $share->setActive($data['active']);

        if ($data['id'] === 'new') {
            $this->entityManager->persist($share);
        }
        $this->entityManager->flush();

        return new JsonResponse(['share' => [
            'id' => $share->getId(),
            'name' => $share->getName(),
            'description' => $share->getDescription(),
            'entities' => $share->getEntities(),
            'members' => $share->getMembers(),
            'active' => $share->isActive(),
        ]]);
    }
}