<?php

namespace App\Controller\Settings\General;

use App\Entity\Authentication\User;
use App\Entity\Messenger\Chat;
use App\Entity\Messenger\Message;
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

    #[Route('/settings/general/share', 'settings_general_share')]
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
            $this->sendValidationMethod($share);

            $this->entityManager->persist($share);
            $this->entityManager->flush();

            return $this->redirectToRoute('settings_general_share');
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

            return $this->redirectToRoute('settings_general_share');
        }

        return $this->render('Page/Settings/General/share-create.html.twig', [
            'id' => $share->getId(),
            'form' => $form->createView()
        ]);
    }

    #[Route('/settings/general/share/remove/{id}', 'settings_general_share_remove', defaults: ['id' => null])]
    public function remove(
        ?Share $share
    ): Response
    {
        if (!$share) {
            return $this->redirectToRoute('settings_general_share');
        }

        $this->entityManager->remove($share);
        $this->entityManager->flush();

        return $this->redirectToRoute('settings_general_share');
    }

    #[Route('/settings/general/share/save', 'settings_general_share_save', methods: ['POST'])]
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
        if (!isset($data['entities']) || $data['entities'] === "") {
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

        $this->sendValidationMethod($share);

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

    #[Route('/settings/general/share/send-validation/{id}', 'settings_general_share_send_validation')]
    public function sendValidation(
        Share $share,
    ): Response
    {
        $this->sendValidationMethod($share);
        $this->entityManager->flush();

        return $this->redirectToRoute('settings_general_share_update', ['id' => $share->getId()]);
    }


    #[Route('/settings/general/share/confirm/{shareId}/{messageId}/{confirm}', 'settings_general_share_confirm')]
    public function confirm(
        int $shareId,
        int $messageId,
        string $confirm
    ): Response
    {
        $share = $this->entityManager->getRepository(Share::class)->find($shareId);
        $message = $this->entityManager->getRepository(Message::class)->find($messageId);

        if (!$share || !$message) {
            throw $this->createNotFoundException();
        }

        $message->setActive(false);
        $validMembers = $share->getValidMembers() ?? [];
        $validMembers[$this->getUser()->getId()] = ($confirm === 'confirm');
        $share->setValidMembers($validMembers);

        $valid = true;
        foreach ($share->getMembers() as $member) {
            $uid = $member->getId();
            if (!array_key_exists($uid, $validMembers) || $validMembers[$uid] !== true) {
                $valid = false;
                break;
            }
        }
        $share->setValid($valid);

        $this->entityManager->flush();

        return $this->redirectToRoute('messenger_chat', ['id' => $message->getChat()->getId()]);
    }
    
    public function sendValidationMethod(Share $share): void
    {
        $me = $this->getUser();

        foreach ($share->getMembers() as $member) {
            if ($member->getId() === $me->getId()) {
                continue; // on ne se DM pas soi-même
            }

            $qb = $this->entityManager->getRepository(Chat::class)->createQueryBuilder('c');
            $qb->leftJoin('c.members', 'm')
                ->where('SIZE(c.members) = 1')
                ->andWhere('(c.owner = :me AND m = :u) OR (c.owner = :u AND m = :me)')
                ->andWhere('c.owner NOT MEMBER OF c.members')
                ->setParameter('me', $me)
                ->setParameter('u', $member)
                ->orderBy('c.id', 'DESC')
                ->setMaxResults(1);

            $chat = $qb->getQuery()->getOneOrNullResult();

            if (!$chat) {
                $chat = new Chat();
                $chat->setOwner($me);
                $chat->addMember($member);
                $chat->setCreatedAt(new \DateTimeImmutable());
                $this->entityManager->persist($chat);
            }

            $message = new Message();
            $message->setChat($chat);
            $message->setCreatedAt(new \DateTimeImmutable());
            $message->setActive(true);
            $message->setAuthor($me);
            $message->setContent('');
            $this->entityManager->persist($message);
            $this->entityManager->flush();

            $confirm_path = '/settings/general/share/confirm/'.$share->getId().'/'.$message->getId().'/confirm';
            $cancel_path  = '/settings/general/share/confirm/'.$share->getId().'/'.$message->getId().'/cancel';
            $message->setContent("[layout][!confirm][confirm_path:'".$confirm_path."',cancel_path:'".$cancel_path."',confirm:'Accepter',cancel:'Refuser']:Message automatique envoyé pour demande de validation de partage");
            $this->entityManager->flush();
        }
    }
}