<?php

namespace App\Security;

use App\Entity\Service;
use App\Entity\Utilisateur;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Droits sur un service (édition réservée RH/Admin, consultation élargie
 * aux responsables du service concerné).
 */
class ServiceVoter extends Voter
{
    public const VOIR = 'SERVICE_VOIR';
    public const EDITER = 'SERVICE_EDITER';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VOIR, self::EDITER], true) && $subject instanceof Service;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $utilisateur = $token->getUser();

        if (!$utilisateur instanceof Utilisateur) {
            return false;
        }

        if (in_array('ROLE_RH', $utilisateur->getRoles(), true)) {
            return true;
        }

        if (self::EDITER === $attribute) {
            return false;
        }

        /** @var Service $service */
        $service = $subject;

        foreach ($utilisateur->getUtilisateurServices() as $utilisateurService) {
            if ($utilisateurService->isEstResponsable() && $utilisateurService->getService() === $service) {
                return true;
            }
        }

        return false;
    }
}
