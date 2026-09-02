<?php

namespace App\Controller\Admin;

use App\Repository\CongeHistoriqueRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Piste d'audit en lecture seule : qui a demandé, qui a validé/refusé,
 * et quand — pour le suivi complet des décisions.
 */
#[Route('/admin/suivi')]
#[IsGranted('ROLE_RH')]
class CongeHistoriqueController extends AbstractController
{
    #[Route('', name: 'admin_suivi_index', methods: ['GET'])]
    public function index(CongeHistoriqueRepository $repository): Response
    {
        return $this->render('admin/suivi/index.html.twig', [
            'historiques' => $repository->findBy([], ['dateAction' => 'DESC']),
        ]);
    }
}
