<?php

namespace App\Service;

use App\Entity\Conge;
use App\Exception\PeriodeChevaucheeException;
use App\Exception\PeriodeInvalideException;
use App\Exception\SoldeInsuffisantException;
use App\Repository\CongeRepository;
use App\Repository\SoldeCongeRepository;

/**
 * Centralise toutes les règles métier de validation d'une demande de congé,
 * exécutées avant toute persistance. Chaque règle échouée lève une
 * exception dédiée, catchée au niveau du contrôleur pour un message clair.
 */
class CongeValidationService
{
    public function __construct(
        private readonly CongeRepository $congeRepository,
        private readonly SoldeCongeRepository $soldeCongeRepository,
    ) {
    }

    /**
     * @throws PeriodeInvalideException
     * @throws PeriodeChevaucheeException
     * @throws SoldeInsuffisantException
     */
    public function validerNouvelleDemande(Conge $conge): void
    {
        $this->verifierCoherencePeriode($conge);
        $this->verifierAbsenceChevauchement($conge);
        $this->verifierSoldeSuffisant($conge);
    }

    private function verifierCoherencePeriode(Conge $conge): void
    {
        $debut = $conge->getDateDebut();
        $fin = $conge->getDateFin();

        if (null === $debut || null === $fin) {
            throw new PeriodeInvalideException('Les dates de début et de fin sont obligatoires.');
        }

        if ($fin < $debut) {
            throw new PeriodeInvalideException('La date de fin ne peut pas être antérieure à la date de début.');
        }

        $aujourdhui = new \DateTimeImmutable('today');
        if ($debut < $aujourdhui) {
            throw new PeriodeInvalideException('Impossible de demander un congé dont la date de début est passée.');
        }
    }

    private function verifierAbsenceChevauchement(Conge $conge): void
    {
        $existants = $this->congeRepository->createQueryBuilder('c')
            ->andWhere('c.utilisateur = :utilisateur')
            ->andWhere('c.statut IN (:statuts)')
            ->andWhere('c.dateDebut <= :fin')
            ->andWhere('c.dateFin >= :debut')
            ->setParameter('utilisateur', $conge->getUtilisateur())
            ->setParameter('statuts', [Conge::STATUT_EN_ATTENTE, Conge::STATUT_VALIDE])
            ->setParameter('debut', $conge->getDateDebut())
            ->setParameter('fin', $conge->getDateFin())
            ->getQuery()
            ->getResult();

        // Exclut la demande elle-même si on est en cours de modification
        $existants = array_filter($existants, fn (Conge $c) => $c->getId() !== $conge->getId());

        if (count($existants) > 0) {
            throw new PeriodeChevaucheeException(
                'Cette période chevauche une demande de congé déjà en attente ou validée.'
            );
        }
    }

    private function verifierSoldeSuffisant(Conge $conge): void
    {
        $annee = (int) $conge->getDateDebut()->format('Y');

        $solde = $this->soldeCongeRepository->findOneBy([
            'utilisateur' => $conge->getUtilisateur(),
            'typeConge' => $conge->getTypeConge(),
            'annee' => $annee,
        ]);

        // Types de congé sans décompte de solde (ex. congé sans solde) : pas de vérification
        if (null === $solde) {
            return;
        }

        $joursDemandes = $conge->getDateDebut()->diff($conge->getDateFin())->days + 1;

        if ($solde->getJoursRestants() < $joursDemandes) {
            throw new SoldeInsuffisantException(sprintf(
                'Solde insuffisant : %s jour(s) disponible(s) pour %s jour(s) demandé(s).',
                $solde->getJoursRestants(),
                $joursDemandes
            ));
        }
    }
}
