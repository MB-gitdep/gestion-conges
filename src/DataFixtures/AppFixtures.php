<?php

namespace App\DataFixtures;

use App\Entity\Entreprise;
use App\Entity\Service;
use App\Entity\TypeConge;
use App\Entity\Utilisateur;
use App\Entity\UtilisateurService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Jeu de données de test : une entreprise, deux services, un admin RH,
 * un responsable, deux employés, et les types de congés courants.
 * Mot de passe pour tous les comptes de test : "password".
 */
class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $entreprise = new Entreprise();
        $entreprise->setNom('Acme SARL')->setSiret('12345678900011');
        $manager->persist($entreprise);

        $serviceCommercial = new Service();
        $serviceCommercial->setNom('Commercial')->setEntreprise($entreprise);
        $manager->persist($serviceCommercial);

        $serviceTech = new Service();
        $serviceTech->setNom('Technique')->setEntreprise($entreprise);
        $manager->persist($serviceTech);

        $admin = $this->creerUtilisateur($manager, 'Admin', 'RH', 'admin@acme.fr', ['ROLE_RH', 'ROLE_ADMIN']);
        $responsable = $this->creerUtilisateur($manager, 'Martin', 'Dupont', 'responsable@acme.fr', ['ROLE_RESPONSABLE']);
        $employe1 = $this->creerUtilisateur($manager, 'Julie', 'Bernard', 'julie@acme.fr', ['ROLE_USER']);
        $employe2 = $this->creerUtilisateur($manager, 'Karim', 'Haddad', 'karim@acme.fr', ['ROLE_USER']);

        $this->affecter($manager, $responsable, $serviceTech, true);
        $this->affecter($manager, $employe1, $serviceTech, false);
        $this->affecter($manager, $employe2, $serviceCommercial, false);

        $typesConges = [
            ['Congés payés', 25],
            ['RTT', 10],
            ['Congé maladie', null],
            ['Congé sans solde', null],
        ];

        foreach ($typesConges as [$nom, $joursDefaut]) {
            $type = new TypeConge();
            $type->setNom($nom)->setJoursDefaut($joursDefaut);
            $manager->persist($type);
        }

        $manager->flush();
    }

    private function creerUtilisateur(ObjectManager $manager, string $prenom, string $nom, string $email, array $roles): Utilisateur
    {
        $utilisateur = new Utilisateur();
        $utilisateur
            ->setPrenom($prenom)
            ->setNom($nom)
            ->setEmail($email)
            ->setRoles($roles)
            ->setStatut(Utilisateur::STATUT_ACTIF)
            ->setDateEntree(new \DateTimeImmutable('-1 year'));

        $utilisateur->setPassword($this->passwordHasher->hashPassword($utilisateur, 'password'));
        $utilisateur->setDoitChangerMotDePasse(false); // comptes de démo, pas de contrainte au premier login

        $manager->persist($utilisateur);

        return $utilisateur;
    }

    private function affecter(ObjectManager $manager, Utilisateur $utilisateur, Service $service, bool $estResponsable): void
    {
        $affectation = new UtilisateurService();
        $affectation
            ->setUtilisateur($utilisateur)
            ->setService($service)
            ->setEstResponsable($estResponsable);

        $manager->persist($affectation);
    }
}
