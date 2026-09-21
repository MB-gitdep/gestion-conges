<?php

namespace App\Controller\Admin;

use App\Entity\Utilisateur;
use App\Entity\UtilisateurService;
use App\Form\UtilisateurServiceType;
use App\Form\UtilisateurType;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/utilisateurs')]
#[IsGranted('ROLE_RH')]
class UtilisateurAdminController extends AbstractController
{
    #[Route('', name: 'admin_utilisateur_index', methods: ['GET'])]
    public function index(UtilisateurRepository $repository): Response
    {
        return $this->render('admin/utilisateur/index.html.twig', [
            'utilisateurs' => $repository->findAll(),
        ]);
    }

    #[Route('/nouveau', name: 'admin_utilisateur_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $utilisateur = new Utilisateur();
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Mot de passe temporaire aléatoire : l'utilisateur le réinitialise
            // via le lien "mot de passe oublié" (à implémenter avec
            // symfony/reset-password-bundle si besoin).
            $motDePasseTemporaire = bin2hex(random_bytes(8));
            $utilisateur->setPassword($passwordHasher->hashPassword($utilisateur, $motDePasseTemporaire));
            $utilisateur->setDoitChangerMotDePasse(true);

            $em->persist($utilisateur);
            $em->flush();
            $this->addFlash('success', sprintf(
                'Utilisateur créé. Mot de passe temporaire à communiquer : %s (il devra le changer à sa première connexion).',
                $motDePasseTemporaire
            ));

            return $this->redirectToRoute('admin_utilisateur_index');
        }

        return $this->render('admin/utilisateur/new.html.twig', ['form' => $form]);
    }

    #[Route('/{id}/modifier', name: 'admin_utilisateur_edit', methods: ['GET', 'POST'])]
    #[IsGranted('UTILISATEUR_EDITER', subject: 'utilisateur')]
    public function edit(Utilisateur $utilisateur, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Utilisateur mis à jour.');

            return $this->redirectToRoute('admin_utilisateur_index');
        }

        return $this->render('admin/utilisateur/edit.html.twig', [
            'form' => $form,
            'utilisateur' => $utilisateur,
        ]);
    }

    /**
     * Gestion des affectations aux services (avec statut "responsable").
     */
    #[Route('/{id}/services', name: 'admin_utilisateur_services', methods: ['GET', 'POST'])]
    #[IsGranted('UTILISATEUR_EDITER', subject: 'utilisateur')]
    public function gererServices(Utilisateur $utilisateur, Request $request, EntityManagerInterface $em): Response
    {
        $nouvelleAffectation = new UtilisateurService();
        $nouvelleAffectation->setUtilisateur($utilisateur);

        $form = $this->createForm(UtilisateurServiceType::class, $nouvelleAffectation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($nouvelleAffectation);
            $em->flush();
            $this->addFlash('success', 'Affectation ajoutée.');

            return $this->redirectToRoute('admin_utilisateur_services', ['id' => $utilisateur->getId()]);
        }

        return $this->render('admin/utilisateur/services.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form,
        ]);
    }

    #[Route('/services/{id}/retirer', name: 'admin_utilisateur_service_retirer', methods: ['POST'])]
    public function retirerService(UtilisateurService $affectation, Request $request, EntityManagerInterface $em): Response
    {
        $utilisateurId = $affectation->getUtilisateur()->getId();

        if ($this->isCsrfTokenValid('retirer' . $affectation->getId(), $request->request->get('_token'))) {
            $em->remove($affectation);
            $em->flush();
            $this->addFlash('success', 'Affectation retirée.');
        }

        return $this->redirectToRoute('admin_utilisateur_services', ['id' => $utilisateurId]);
    }
	#[Route('/{id}/supprimer', name: 'admin_utilisateur_delete', methods: ['POST'])]
    #[IsGranted('UTILISATEUR_GERER_DROITS', subject: 'utilisateur')]
    public function delete(Utilisateur $utilisateur, Request $request, EntityManagerInterface $em): Response
    {
        // Un admin ne peut pas se supprimer lui-même (évite de se retrouver
        // sans accès admin par erreur de clic)
        if ($utilisateur === $this->getUser()) {
            $this->addFlash('error', 'Vous ne pouvez pas supprimer votre propre compte.');

            return $this->redirectToRoute('admin_utilisateur_index');
        }

        if ($this->isCsrfTokenValid('delete' . $utilisateur->getId(), $request->request->get('_token'))) {
            $em->remove($utilisateur);
            $em->flush();
            $this->addFlash('success', 'Utilisateur supprimé.');
        }

        return $this->redirectToRoute('admin_utilisateur_index');
    }
}
