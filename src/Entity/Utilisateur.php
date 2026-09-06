<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Un utilisateur de l'application : employé, responsable, RH ou admin
 * selon les rôles qui lui sont attribués (aucun rôle dédié séparé —
 * c'est le champ "roles" qui détermine ce qu'il peut faire, combiné à
 * la hiérarchie définie dans security.yaml). Sert aussi d'entité
 * d'authentification Symfony (UserInterface).
 */
#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\Table(name: '`utilisateur`')]
#[ORM\UniqueConstraint(name: 'UNIQ_EMAIL', fields: ['email'])]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const STATUT_ACTIF = 'actif';
    public const STATUT_INACTIF = 'inactif';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    private ?\DateTimeImmutable $dateEntree = null;

    #[ORM\Column(length: 20)]
    private string $statut = self::STATUT_ACTIF;

    // Vrai tant que l'utilisateur n'a pas remplacé son mot de passe
    // temporaire (attribué par un admin) par un mot de passe de son
    // choix — voir ForcerChangementMotDePasseSubscriber, qui bloque
    // l'accès à toute autre page tant que ce flag est vrai.
    #[ORM\Column]
    private bool $doitChangerMotDePasse = true;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: UtilisateurService::class, orphanRemoval: true)]
    private Collection $utilisateurServices;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: Conge::class, orphanRemoval: true)]
    private Collection $congesDemandes;

    #[ORM\OneToMany(mappedBy: 'validateur', targetEntity: Conge::class)]
    private Collection $congesValides;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: SoldeConge::class, orphanRemoval: true)]
    private Collection $soldesConges;

    public function __construct()
    {
        $this->utilisateurServices = new ArrayCollection();
        $this->congesDemandes = new ArrayCollection();
        $this->congesValides = new ArrayCollection();
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // garantit que chaque utilisateur a au moins ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // efface les données sensibles temporaires stockées sur l'utilisateur, si besoin
    }

    public function getDateEntree(): ?\DateTimeImmutable
    {
        return $this->dateEntree;
    }

    public function setDateEntree(?\DateTimeImmutable $dateEntree): static
    {
        $this->dateEntree = $dateEntree;

        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function doitChangerMotDePasse(): bool
    {
        return $this->doitChangerMotDePasse;
    }

    public function setDoitChangerMotDePasse(bool $doitChangerMotDePasse): static
    {
        $this->doitChangerMotDePasse = $doitChangerMotDePasse;

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
            $utilisateurService->setUtilisateur($this);
        }

        return $this;
    }

    public function removeUtilisateurService(UtilisateurService $utilisateurService): static
    {
        if ($this->utilisateurServices->removeElement($utilisateurService)) {
            if ($utilisateurService->getUtilisateur() === $this) {
                $utilisateurService->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Conge>
     */
    public function getCongesDemandes(): Collection
    {
        return $this->congesDemandes;
    }

    public function addCongesDemande(Conge $conge): static
    {
        if (!$this->congesDemandes->contains($conge)) {
            $this->congesDemandes->add($conge);
            $conge->setUtilisateur($this);
        }

        return $this;
    }

    public function removeCongesDemande(Conge $conge): static
    {
        if ($this->congesDemandes->removeElement($conge)) {
            if ($conge->getUtilisateur() === $this) {
                $conge->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Conge>
     */
    public function getCongesValides(): Collection
    {
        return $this->congesValides;
    }

    public function addCongesValide(Conge $conge): static
    {
        if (!$this->congesValides->contains($conge)) {
            $this->congesValides->add($conge);
            $conge->setValidateur($this);
        }

        return $this;
    }

    public function removeCongesValide(Conge $conge): static
    {
        if ($this->congesValides->removeElement($conge)) {
            if ($conge->getValidateur() === $this) {
                $conge->setValidateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SoldeConge>
     */
    public function getSoldesConges(): Collection
    {
        return $this->soldesConges;
    }

    public function addSoldesConge(SoldeConge $soldeConge): static
    {
        if (!$this->soldesConges->contains($soldeConge)) {
            $this->soldesConges->add($soldeConge);
            $soldeConge->setUtilisateur($this);
        }

        return $this;
    }

    public function removeSoldesConge(SoldeConge $soldeConge): static
    {
        if ($this->soldesConges->removeElement($soldeConge)) {
            if ($soldeConge->getUtilisateur() === $this) {
                $soldeConge->setUtilisateur(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return trim(($this->prenom ?? '') . ' ' . ($this->nom ?? ''));
    }
}
