<?php

namespace App\Entity;

use App\Repository\TypeCongeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Un type de congé paramétrable par l'admin (ex. "Congés payés", "RTT",
 * "Congé sans solde"). Le champ joursDefaut sert de référence pour
 * initialiser les soldes annuels, mais n'a aucun effet automatique —
 * c'est bien SoldeConge qui fait foi pour savoir combien de jours
 * il reste réellement à un utilisateur donné.
 */
#[ORM\Entity(repositoryClass: TypeCongeRepository::class)]
class TypeConge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(nullable: true)]
    private ?int $joursDefaut = null;

    #[ORM\OneToMany(mappedBy: 'typeConge', targetEntity: Conge::class)]
    private Collection $conges;

    #[ORM\OneToMany(mappedBy: 'typeConge', targetEntity: SoldeConge::class)]
    private Collection $soldesConges;

    public function __construct()
    {
        $this->conges = new ArrayCollection();
        $this->soldesConges = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getJoursDefaut(): ?int
    {
        return $this->joursDefaut;
    }

    public function setJoursDefaut(?int $joursDefaut): static
    {
        $this->joursDefaut = $joursDefaut;

        return $this;
    }

    /**
     * @return Collection<int, Conge>
     */
    public function getConges(): Collection
    {
        return $this->conges;
    }

    /**
     * @return Collection<int, SoldeConge>
     */
    public function getSoldesConges(): Collection
    {
        return $this->soldesConges;
    }

    public function __toString(): string
    {
        return $this->nom ?? '';
    }
}
