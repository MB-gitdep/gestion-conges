<?php

namespace App\Entity;

use App\Repository\CongeHistoriqueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CongeHistoriqueRepository::class)]
class CongeHistorique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'historiques')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Conge $conge = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\Column(length: 20)]
    private ?string $ancienStatut = null;

    #[ORM\Column(length: 20)]
    private ?string $nouveauStatut = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateAction = null;

    public function __construct()
    {
        $this->dateAction = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConge(): ?Conge
    {
        return $this->conge;
    }

    public function setConge(?Conge $conge): static
    {
        $this->conge = $conge;

        return $this;
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

    public function getAncienStatut(): ?string
    {
        return $this->ancienStatut;
    }

    public function setAncienStatut(string $ancienStatut): static
    {
        $this->ancienStatut = $ancienStatut;

        return $this;
    }

    public function getNouveauStatut(): ?string
    {
        return $this->nouveauStatut;
    }

    public function setNouveauStatut(string $nouveauStatut): static
    {
        $this->nouveauStatut = $nouveauStatut;

        return $this;
    }

    public function getDateAction(): ?\DateTimeImmutable
    {
        return $this->dateAction;
    }

    public function setDateAction(\DateTimeImmutable $dateAction): static
    {
        $this->dateAction = $dateAction;

        return $this;
    }
}
