<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\ChangerMotDePasseType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profil')]
#[IsGranted('ROLE_USER')]
class ProfilController extends AbstractController
{
    #[Route('/mot-de-passe', name: 'profil_changer_mot_de_passe', methods: ['GET', 'POST'])]
    public function changerMotDePasse(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        LoggerInterface $logger
    ): Response {
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getUser();

        $form = $this->createForm(ChangerMotDePasseType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $motDePasseActuel = $form->get('motDePasseActuel')->getData();

            if (!$passwordHasher->isPasswordValid($utilisateur, $motDePasseActuel)) {
                // Volontairement générique : ne jamais confirmer/infirmer
                // quelle partie de la saisie est fausse (anti-énumération)
                $form->get('motDePasseActuel')->addError(
                    new \Symfony\Component\Form\FormError('Mot de passe actuel incorrect.')
                );

                $logger->warning('Tentative de changement de mot de passe avec mot de passe actuel invalide', [
                    'utilisateur' => $utilisateur->getUserIdentifier(),
                ]);
            } else {
                $nouveauMotDePasse = $form->get('nouveauMotDePasse')->getData();

                $utilisateur->setPassword($passwordHasher->hashPassword($utilisateur, $nouveauMotDePasse));
                $utilisateur->setDoitChangerMotDePasse(false);
                $em->flush();

                $this->addFlash('success', 'Votre mot de passe a été mis à jour.');

                return $this->redirectToRoute('conge_index');
            }
        }

        return $this->render('profil/changer_mot_de_passe.html.twig', [
            'form' => $form,
            'premiereConnexion' => $utilisateur->doitChangerMotDePasse(),
        ]);
    }
}
