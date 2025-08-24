<?php

namespace App\Controller\Settings\Profile;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    #[Route('/settings/profile', name: 'settings_profile')]
    public function index(): Response
    {
        return $this->redirectToRoute('settings_profile_general');
    }
}