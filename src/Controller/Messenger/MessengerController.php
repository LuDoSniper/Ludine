<?php

namespace App\Controller\Messenger;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MessengerController extends AbstractController
{
    #[Route('/messenger', name: 'messenger')]
    public function index(): Response
    {
        return $this->redirectToRoute('messenger_chat_idle');
    }
}