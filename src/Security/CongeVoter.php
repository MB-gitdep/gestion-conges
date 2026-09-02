<?php

namespace App\Security;

use App\Entity\Conge;
use App\Entity\Utilisateur;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CongeVoter extends Voter
{
    public const VALIDER = 'CONGE_VALIDER';
    public const VOIR = 'CONGE_VOIR';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VALIDER, self::VOIR], true) && $subject instanceof Conge;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $utilisateur = $token->getUser();

        if (!$utilisateur instanceof Utilisateur) {
            return false;
        }

        // RH/Admin peuvent toujours voir et valider, quel que soit le service
        if (in_array('ROLE_RH', $utilisateur->getRoles(), true)) {
            return true;
        }

        /** @var Conge $conge */
        $conge = $subject;
        $demandeur = $conge->getUtilisateur();

        // Le demandeur peut toujours consulter sa propre demande,
        // mais jamais la valider lui-même
        if ($demandeur === $utilisateur) {
            return self::VOIR === $attribute;
        }

        // Vérifie que l'utilisateur est responsable d'au moins un service
        // partagé avec le demandeur (droit de voir ET de valider)
        $servicesDemandeur = array_map(
            fn ($us) => $us->getService(),
            $demandeur->getUtilisateurServices()->toArray()
        );

        foreach ($utilisateur->getUtilisateurServices() as $utilisateurService) {
            if ($utilisateurService->isEstResponsable()
                && in_array($utilisateurService->getService(), $servicesDemandeur, true)
            ) {
                return true;
            }
        }

        return false;
    }
}
