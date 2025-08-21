<?php

namespace App\Controller\Settings\General;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GeneralController extends AbstractController
{
    #[Route('/settings/general', name: 'settings_general')]
    public function index(): Response
    {
        return $this->redirectToRoute('settings_general_shares');
    }
}