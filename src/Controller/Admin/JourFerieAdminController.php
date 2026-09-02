<?php

namespace App\Controller\Admin;

use App\Entity\JourFerie;
use App\Form\JourFerieType;
use App\Repository\JourFerieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Gestion du calendrier des jours fériés (par entreprise).
 */
#[Route('/admin/calendrier')]
#[IsGranted('ROLE_RH')]
class JourFerieAdminController extends AbstractController
{
    #[Route('', name: 'admin_jour_ferie_index', methods: ['GET'])]
    public function index(JourFerieRepository $repository): Response
    {
        return $this->render('admin/jour_ferie/index.html.twig', [
            'joursFeries' => $repository->findBy([], ['date' => 'ASC']),
        ]);
    }

    #[Route('/nouveau', name: 'admin_jour_ferie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $jourFerie = new JourFerie();
        $form = $this->createForm(JourFerieType::class, $jourFerie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($jourFerie);
            $em->flush();
            $this->addFlash('success', 'Jour férié ajouté au calendrier.');

            return $this->redirectToRoute('admin_jour_ferie_index');
        }

        return $this->render('admin/jour_ferie/new.html.twig', ['form' => $form]);
    }

    #[Route('/{id}/supprimer', name: 'admin_jour_ferie_delete', methods: ['POST'])]
    public function delete(JourFerie $jourFerie, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $jourFerie->getId(), $request->request->get('_token'))) {
            $em->remove($jourFerie);
            $em->flush();
            $this->addFlash('success', 'Jour férié supprimé.');
        }

        return $this->redirectToRoute('admin_jour_ferie_index');
    }
}
