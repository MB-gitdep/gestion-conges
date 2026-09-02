<?php

namespace App\Repository;

use App\Entity\Conge;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Conge>
 */
class CongeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conge::class);
    }

    /**
     * Renvoie les demandes "en_attente" des services dont $responsable
     * est déclaré responsable (hors ses propres demandes).
     *
     * @return Conge[]
     */
    public function findEnAttentePourResponsable(Utilisateur $responsable): array
    {
        $serviceIds = [];
        foreach ($responsable->getUtilisateurServices() as $utilisateurService) {
            if ($utilisateurService->isEstResponsable()) {
                $serviceIds[] = $utilisateurService->getService()->getId();
            }
        }

        if (empty($serviceIds)) {
            return [];
        }

        return $this->createQueryBuilder('c')
            ->innerJoin('c.utilisateur', 'demandeur')
            ->innerJoin('demandeur.utilisateurServices', 'us')
            ->andWhere('c.statut = :statut')
            ->andWhere('us.service IN (:serviceIds)')
            ->andWhere('c.utilisateur != :responsable')
            ->setParameter('statut', Conge::STATUT_EN_ATTENTE)
            ->setParameter('serviceIds', $serviceIds)
            ->setParameter('responsable', $responsable)
            ->orderBy('c.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
