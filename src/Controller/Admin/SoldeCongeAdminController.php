<?php

namespace App\Controller\Admin;

use App\Entity\SoldeConge;
use App\Form\SoldeCongeType;
use App\Repository\SoldeCongeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/soldes')]
#[IsGranted('ROLE_RH')]
class SoldeCongeAdminController extends AbstractController
{
    #[Route('', name: 'admin_solde_index', methods: ['GET'])]
    public function index(SoldeCongeRepository $repository): Response
    {
        return $this->render('admin/solde/index.html.twig', [
            'soldes' => $repository->findAll(),
        ]);
    }

    #[Route('/nouveau', name: 'admin_solde_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $solde = new SoldeConge();
        $form = $this->createForm(SoldeCongeType::class, $solde);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($solde);
            $em->flush();
            $this->addFlash('success', 'Solde créé.');

            return $this->redirectToRoute('admin_solde_index');
        }

        return $this->render('admin/solde/new.html.twig', ['form' => $form]);
    }

    #[Route('/{id}/modifier', name: 'admin_solde_edit', methods: ['GET', 'POST'])]
    public function edit(SoldeConge $solde, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(SoldeCongeType::class, $solde);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Solde mis à jour.');

            return $this->redirectToRoute('admin_solde_index');
        }

        return $this->render('admin/solde/edit.html.twig', ['form' => $form, 'solde' => $solde]);
    }
}
