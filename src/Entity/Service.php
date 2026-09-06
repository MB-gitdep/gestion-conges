<?php

namespace App\Entity;

use App\Repository\ServiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Un service (département) au sein d'une entreprise, ex. "Commercial",
 * "Technique". Un utilisateur peut appartenir à plusieurs services à
 * la fois (voir UtilisateurService), et être responsable de certains
 * d'entre eux sans l'être des autres.
 */
#[ORM\Entity(repositoryClass: ServiceRepository::class)]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\ManyToOne(inversedBy: 'services')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entreprise $entreprise = null;

    #[ORM\OneToMany(mappedBy: 'service', targetEntity: UtilisateurService::class, orphanRemoval: true)]
    private Collection $utilisateurServices;

    public function __construct()
    {
        $this->utilisateurServices = new ArrayCollection();
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

    public function getEntreprise(): ?Entreprise
    {
        return $this->entreprise;
    }

    public function setEntreprise(?Entreprise $entreprise): static
    {
        $this->entreprise = $entreprise;

        return $this;
    }

    /**
     * @return Collection<int, UtilisateurService>
     */
    public function getUtilisateurServices(): Collection
    {
        return $this->utilisateurServices;
    }

    public function addUtilisateurService(UtilisateurService $utilisateurService): static
    {
        if (!$this->utilisateurServices->contains($utilisateurService)) {
            $this->utilisateurServices->add($utilisateurService);
            $utilisateurService->setService($this);
        }

        return $this;
    }

    public function removeUtilisateurService(UtilisateurService $utilisateurService): static
    {
        if ($this->utilisateurServices->removeElement($utilisateurService)) {
            if ($utilisateurService->getService() === $this) {
                $utilisateurService->setService(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->nom ?? '';
    }
}
