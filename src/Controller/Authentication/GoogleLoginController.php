<?php

namespace App\Controller\Authentication;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class GoogleLoginController extends AbstractController
{
    #[Route('/login/google', 'app_login_google')]
    public function connect(ClientRegistry $clientRegistry)
    {
        // Redirection vers la page de login Google
        $client = $clientRegistry->getClient('google');
        return $client->redirect(['email']);
    }

    #[Route('/login/google/check', 'app_login_google_check')]
    public function connectCheck()
    {
        // Le bundle gère le reste de la logique de connexion
    }
}