<?php

namespace App\Controller\Admin;

use App\Repository\CongeRepository;
use App\Repository\ServiceRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_RH')]
class AdminDashboardController extends AbstractController
{
    #[Route('', name: 'admin_dashboard', methods: ['GET'])]
    public function index(
        UtilisateurRepository $utilisateurRepository,
        ServiceRepository $serviceRepository,
        CongeRepository $congeRepository
    ): Response {
        return $this->render('admin/dashboard.html.twig', [
            'nbUtilisateurs' => count($utilisateurRepository->findAll()),
            'nbServices' => count($serviceRepository->findAll()),
            'demandesEnAttente' => $congeRepository->findBy(['statut' => 'en_attente']),
        ]);
    }
}
