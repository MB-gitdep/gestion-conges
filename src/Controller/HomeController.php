<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Route racine de l'application : redirige vers l'espace personnel si
 * connecté, vers la page de connexion sinon. Évite un 404 sur "/".
 */
class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): RedirectResponse
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('conge_index');
        }

        return $this->redirectToRoute('app_login');
    }
}
