<?php

namespace App\Security;

use App\Entity\Utilisateur;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Droits sur la fiche d'un utilisateur (consultation d'équipe, édition admin).
 */
class UtilisateurVoter extends Voter
{
    public const VOIR = 'UTILISATEUR_VOIR';
    public const EDITER = 'UTILISATEUR_EDITER';
    public const GERER_DROITS = 'UTILISATEUR_GERER_DROITS';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VOIR, self::EDITER, self::GERER_DROITS], true)
            && $subject instanceof Utilisateur;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $connecte = $token->getUser();

        if (!$connecte instanceof Utilisateur) {
            return false;
        }

        /** @var Utilisateur $cible */
        $cible = $subject;

        // Seul RH/Admin peut éditer une fiche ou modifier des droits
        if (in_array($attribute, [self::EDITER, self::GERER_DROITS], true)) {
            return in_array('ROLE_RH', $connecte->getRoles(), true);
        }

        // VOIR : soi-même, RH/Admin, ou un responsable pour un membre de son service
        if (self::VOIR === $attribute) {
            if ($connecte === $cible || in_array('ROLE_RH', $connecte->getRoles(), true)) {
                return true;
            }

            $servicesCible = array_map(
                fn ($us) => $us->getService(),
                $cible->getUtilisateurServices()->toArray()
            );

            foreach ($connecte->getUtilisateurServices() as $utilisateurService) {
                if ($utilisateurService->isEstResponsable()
                    && in_array($utilisateurService->getService(), $servicesCible, true)
                ) {
                    return true;
                }
            }
        }

        return false;
    }
}
