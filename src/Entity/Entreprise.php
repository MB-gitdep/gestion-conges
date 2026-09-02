<?php

namespace App\Entity;

use App\Repository\EntrepriseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EntrepriseRepository::class)]
class Entreprise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 14, nullable: true)]
    private ?string $siret = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

    #[ORM\OneToMany(mappedBy: 'entreprise', targetEntity: Service::class, orphanRemoval: true)]
    private Collection $services;

    #[ORM\OneToMany(mappedBy: 'entreprise', targetEntity: JourFerie::class, orphanRemoval: true)]
    private Collection $joursFeries;

    public function __construct()
    {
        $this->services = new ArrayCollection();
        $this->joursFeries = new ArrayCollection();
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

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(?string $siret): static
    {
        $this->siret = $siret;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    /**
     * @return Collection<int, Service>
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Service $service): static
    {
        if (!$this->services->contains($service)) {
            $this->services->add($service);
            $service->setEntreprise($this);
        }

        return $this;
    }

    public function removeService(Service $service): static
    {
        if ($this->services->removeElement($service)) {
            if ($service->getEntreprise() === $this) {
                $service->setEntreprise(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, JourFerie>
     */
    public function getJoursFeries(): Collection
    {
        return $this->joursFeries;
    }

    public function addJourFerie(JourFerie $jourFerie): static
    {
        if (!$this->joursFeries->contains($jourFerie)) {
            $this->joursFeries->add($jourFerie);
            $jourFerie->setEntreprise($this);
        }

        return $this;
    }

    public function removeJourFerie(JourFerie $jourFerie): static
    {
        if ($this->joursFeries->removeElement($jourFerie)) {
            if ($jourFerie->getEntreprise() === $this) {
                $jourFerie->setEntreprise(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->nom ?? '';
    }
}
