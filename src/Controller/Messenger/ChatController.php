<?php

namespace App\Controller\Messenger;

use App\Entity\Messenger\Chat;
use App\Entity\Messenger\Message;
use App\Form\Messenger\ChatType;
use App\Service\EntityService;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ChatController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EntityService $entityService,
    ){}

    #[Route('/messenger/chat/idle', name: 'messenger_chat_idle')]
    public function idle(): Response
    {
        $qb = $this->entityManager->getRepository(Chat::class)->createQueryBuilder('c');
        $qb
            ->leftJoin('c.messages', 'm') // relation OneToMany Chat->messages
            ->andWhere(':user MEMBER OF c.members OR :user = c.owner')
            ->setParameter('user', $this->getUser())
            ->addSelect('MAX(m.created_at) AS HIDDEN lastMessageAt')
            ->groupBy('c.id')
            ->orderBy('lastMessageAt', 'DESC')
        ;
        $chats = $qb->getQuery()->getResult();

        return $this->render('Page/Messenger/idle.html.twig', [
            'chats' => $chats,
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/messenger/chat/create', name: 'messenger_chat_create')]
    public function create(
        Request $request,
    ): Response
    {
        $chat = new Chat();
        $chat->setOwner($this->getUser());
        $chat->setCreatedAt(new \DateTimeImmutable());

        $form = $this->createForm(ChatType::class, $chat, [
            'user' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        $invalidMembers = false;
        if ($form->isSubmitted() && $form->isValid()) {
            $members = $form->get('members')->getData();
            $invalidMembers = $members instanceof Collection ? $members->isEmpty() : empty($members);
            if (!$invalidMembers && $form->get('name')->getData() === null && !$chat->isMP()) {
                    $chat->setName($chat->getMembers()[0]->getDisplayName() ?: $chat->getMembers()[0]->getUsername());
            }

            if(!$invalidMembers) {
                $this->entityManager->persist($chat);
                $this->entityManager->flush();

                return $this->redirectToRoute('messenger_chat_idle');
            }
        }

        $qb = $this->entityManager->getRepository(Chat::class)->createQueryBuilder('c');
        $qb
            ->leftJoin('c.messages', 'm') // relation OneToMany Chat->messages
            ->andWhere(':user MEMBER OF c.members OR :user = c.owner')
            ->setParameter('user', $this->getUser())
            ->addSelect('MAX(m.created_at) AS HIDDEN lastMessageAt')
            ->groupBy('c.id')
            ->orderBy('lastMessageAt', 'DESC')
        ;
        $chats = $qb->getQuery()->getResult();

        return $this->render('Page/Messenger/chat-create.html.twig', [
            'form' => $form->createView(),
            'chats' => $chats,
            'invalidMembers' => $invalidMembers,
        ]);
    }

    #[Route('/messenger/chat/remove/{id}', name: 'messenger_chat_remove')]
    public function remove(
        Chat $chat,
    ): Response
    {
        $this->entityManager->remove($chat);
        $this->entityManager->flush();

        return $this->redirectToRoute('messenger_chat_idle');
    }

    #[Route('/messenger/chat/update/{id}', name: 'messenger_chat_update')]
    public function update(
        Chat $chat,
        Request $request,
    ): Response
    {

        $form = $this->createForm(ChatType::class, $chat, [
            'user' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        $invalidMembers = false;
        if ($form->isSubmitted() && $form->isValid()) {
            $members = $form->get('members')->getData();
            $invalidMembers = $members instanceof Collection ? $members->isEmpty() : empty($members);
            if (!$invalidMembers && $form->get('name')->getData() === null && !$chat->isMP()) {
                $chat->setName($chat->getMembers()[0]->getDisplayName() ?: $chat->getMembers()[0]->getUsername());
            }

            if(!$invalidMembers) {
                $this->entityManager->persist($chat);
                $this->entityManager->flush();

                return $this->redirectToRoute('messenger_chat_idle');
            }
        }

        $qb = $this->entityManager->getRepository(Chat::class)->createQueryBuilder('c');
        $qb
            ->leftJoin('c.messages', 'm') // relation OneToMany Chat->messages
            ->andWhere(':user MEMBER OF c.members OR :user = c.owner')
            ->setParameter('user', $this->getUser())
            ->addSelect('MAX(m.created_at) AS HIDDEN lastMessageAt')
            ->groupBy('c.id')
            ->orderBy('lastMessageAt', 'DESC')
        ;
        $chats = $qb->getQuery()->getResult();

        return $this->render('Page/Messenger/chat-create.html.twig', [
            'form' => $form->createView(),
            'chats' => $chats,
            'invalidMembers' => $invalidMembers,
        ]);
    }

    #[Route('/messenger/chat/api/{id}/history', name: 'messenger_chat_api_history', methods: ['GET'])]
    public function history(
        Chat $chat,
        Request $request
    ): JsonResponse
    {
        $limit = max(1, min(200, (int) $request->query->get('limit', 50)));
        $items = $this->entityManager->getRepository(Message::class)->findBy(['chat' => $chat->getId(), 'active' => true], ['created_at' => 'ASC'], $limit);
        return $this->json(array_map($this->normalize(...), $items));
    }

    #[Route('/messenger/chat/api/{id}/pull/{lastId}', name: 'messenger_chat_api_pull', methods: ['GET'], defaults: ['lastId' => null])]
    public function pull(
        Chat $chat,
        ?int $lastId,
        Request $request
    ): JsonResponse
    {
        $limit = max(1, min(200, (int) $request->query->get('limit', 50)));

        // 1) On détermine la borne "after"
        $after = null;
        if ($lastId && $lastId > 0) {
            $lastMessage = $this->entityManager->getRepository(Message::class)->find($lastId);
            // si l'id ne correspond à aucun message, on retombe à "pas de borne"
            if ($lastMessage) {
                $after = $lastMessage->getCreatedAt(); // \DateTimeImmutable
            }
        }
        // Optionnel: sinon, démarre "au début des temps"
        if ($after === null) {
            $after = new \DateTimeImmutable('@0'); // 1970-01-01
        }

        // 2) On requête avec un > createdAt pour ce chat
        $qb = $this->entityManager->getRepository(Message::class)->createQueryBuilder('m');
        $qb->andWhere('m.chat = :chat')
            ->andWhere('m.active = :active')
            ->andWhere('m.created_at > :after')      // <<< le filtre date strictement après
            ->setParameter('chat', $chat)           // tu peux aussi mettre $chat->getId() selon ton mapping
            ->setParameter('active', true)
            ->setParameter('after', $after)
            ->orderBy('m.created_at', 'ASC')
            ->setMaxResults($limit);

        $items = $qb->getQuery()->getResult();

        return $this->json(array_map($this->normalize(...), $items));
    }

    #[Route('/messenger/chat/api/{id}/post', name: 'messenger_chat_api_post', methods: ['POST'])]
    public function post(
        Chat $chat,
        Request $request
    ): JsonResponse
    {
        $data = json_decode($request->getContent() ?: '[]', true) ?: [];
        $author = $this->getUser();
        $content = trim((string) ($data['content'] ?? ''));

        if ($content === '') {
            return $this->json(['error' => 'empty content'], 400);
        }

        $m = (new Message())
            ->setChat($chat)
            ->setAuthor($author)
            ->setContent($content)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setActive(true)
        ;

        $this->entityManager->persist($m);
        $this->entityManager->flush();

        // Pas besoin de publier quoi que ce soit : les clients récupèrent au prochain "events"
        return $this->json(['ok' => true, 'id' => $m->getId()]);
    }

    private function normalize(Message $m): array
    {
        return [
            'id' => $m->getId(),
            'chat_id' => $m->getChat()->getId(),
            'author' => [
                'id' => $m->getAuthor()->getId(),
                'name' => $m->getAuthor()->getDisplayName() ?: $m->getAuthor()->getUsername(),
            ],
            'content' => $m->getContent(),
            'created_at' => $m->getCreatedAt()->format(DATE_ATOM),
        ];
    }

    #[Route('/messenger/chat/{id}', name: 'messenger_chat')]
    public function chat(
        Chat $chat,
    ): Response
    {
        $qb = $this->entityManager->getRepository(Chat::class)->createQueryBuilder('c');
        $qb
            ->leftJoin('c.messages', 'm') // relation OneToMany Chat->messages
            ->andWhere(':user MEMBER OF c.members OR :user = c.owner')
            ->setParameter('user', $this->getUser())
            ->addSelect('MAX(m.created_at) AS HIDDEN lastMessageAt')
            ->groupBy('c.id')
            ->orderBy('lastMessageAt', 'DESC')
        ;
        $chats = $qb->getQuery()->getResult();

        return $this->render('Page/Messenger/chat.html.twig', [
            'chats' => $chats,
            'chat' => $chat,
            'user' => $this->getUser(),
            'isMP' => $chat->isMP(),
        ]);
    }
}