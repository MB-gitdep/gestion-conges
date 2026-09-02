<?php

namespace App\Entity;

use App\Repository\UtilisateurServiceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UtilisateurServiceRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_UTILISATEUR_SERVICE', fields: ['utilisateur', 'service'])]
class UtilisateurService
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'utilisateurServices')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\ManyToOne(inversedBy: 'utilisateurServices')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Service $service = null;

    #[ORM\Column]
    private bool $estResponsable = false;

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

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): static
    {
        $this->service = $service;

        return $this;
    }

    public function isEstResponsable(): bool
    {
        return $this->estResponsable;
    }

    public function setEstResponsable(bool $estResponsable): static
    {
        $this->estResponsable = $estResponsable;

        return $this;
    }
}
