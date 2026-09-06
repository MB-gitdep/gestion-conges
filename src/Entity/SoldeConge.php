<?php

namespace App\Entity;

use App\Repository\SoldeCongeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Le nombre de jours restants pour un utilisateur, un type de congé
 * et une année donnés. Volontairement annuel (pas de solde "glissant")
 * pour permettre de gérer les reliquats reportés d'une année sur
 * l'autre comme des lignes distinctes si besoin.
 */
#[ORM\Entity(repositoryClass: SoldeCongeRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_SOLDE_UTILISATEUR_TYPE_ANNEE', fields: ['utilisateur', 'typeConge', 'annee'])]
class SoldeConge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'soldesConges')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(inversedBy: 'soldesConges')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TypeConge $typeConge = null;

    #[ORM\Column]
    private ?int $annee = null;

    #[ORM\Column(type: 'float')]
    private ?float $joursRestants = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getTypeConge(): ?TypeConge
    {
        return $this->typeConge;
    }

    public function setTypeConge(?TypeConge $typeConge): static
    {
        $this->typeConge = $typeConge;

        return $this;
    }

    public function getAnnee(): ?int
    {
        return $this->annee;
    }

    public function setAnnee(int $annee): static
    {
        $this->annee = $annee;

        return $this;
    }

    public function getJoursRestants(): ?float
    {
        return $this->joursRestants;
    }

    public function setJoursRestants(float $joursRestants): static
    {
        $this->joursRestants = $joursRestants;

        return $this;
    }
}
