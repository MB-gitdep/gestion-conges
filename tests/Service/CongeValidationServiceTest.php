<?php

namespace App\Tests\Service;

use App\Entity\Conge;
use App\Entity\TypeConge;
use App\Entity\Utilisateur;
use App\Exception\PeriodeChevaucheeException;
use App\Exception\PeriodeInvalideException;
use App\Exception\SoldeInsuffisantException;
use App\Repository\CongeRepository;
use App\Repository\SoldeCongeRepository;
use App\Service\CongeValidationService;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Vérifie que chaque règle métier de CongeValidationService lève bien
 * l'exception attendue, et qu'une demande valide ne lève rien.
 */
class CongeValidationServiceTest extends TestCase
{
    private function creerConge(\DateTimeImmutable $debut, \DateTimeImmutable $fin): Conge
    {
        $utilisateur = new Utilisateur();
        $typeConge = new TypeConge();

        $conge = new Conge();
        $conge->setUtilisateur($utilisateur);
        $conge->setTypeConge($typeConge);
        $conge->setDateDebut($debut);
        $conge->setDateFin($fin);

        return $conge;
    }

    private function creerService(array $congesExistants = [], ?object $solde = null): CongeValidationService
    {
        $congeRepository = $this->createMock(CongeRepository::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(\Doctrine\ORM\AbstractQuery::class);

        $queryBuilder->method('andWhere')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);
        $query->method('getResult')->willReturn($congesExistants);
        $congeRepository->method('createQueryBuilder')->willReturn($queryBuilder);

        $soldeRepository = $this->createMock(SoldeCongeRepository::class);
        $soldeRepository->method('findOneBy')->willReturn($solde);

        return new CongeValidationService($congeRepository, $soldeRepository);
    }

    public function testLeveExceptionSiDateFinAvantDateDebut(): void
    {
        $this->expectException(PeriodeInvalideException::class);

        $conge = $this->creerConge(
            new \DateTimeImmutable('+10 days'),
            new \DateTimeImmutable('+5 days')
        );

        $this->creerService()->validerNouvelleDemande($conge);
    }

    public function testLeveExceptionSiDateDebutDansLePasse(): void
    {
        $this->expectException(PeriodeInvalideException::class);

        $conge = $this->creerConge(
            new \DateTimeImmutable('-5 days'),
            new \DateTimeImmutable('+5 days')
        );

        $this->creerService()->validerNouvelleDemande($conge);
    }

    public function testLeveExceptionSiPeriodeChevauchee(): void
    {
        $this->expectException(PeriodeChevaucheeException::class);

        $conge = $this->creerConge(
            new \DateTimeImmutable('+10 days'),
            new \DateTimeImmutable('+15 days')
        );

        $congeExistant = $this->creerConge(
            new \DateTimeImmutable('+12 days'),
            new \DateTimeImmutable('+18 days')
        );

        $this->creerService([$congeExistant])->validerNouvelleDemande($conge);
    }

    public function testLeveExceptionSiSoldeInsuffisant(): void
    {
        $this->expectException(SoldeInsuffisantException::class);

        $conge = $this->creerConge(
            new \DateTimeImmutable('+10 days'),
            new \DateTimeImmutable('+20 days') // 11 jours demandés
        );

        $solde = new class {
            public function getJoursRestants(): float
            {
                return 2.0; // insuffisant
            }
        };

        $this->creerService([], $solde)->validerNouvelleDemande($conge);
    }

    public function testNeLeveRienSiDemandeValide(): void
    {
        $conge = $this->creerConge(
            new \DateTimeImmutable('+10 days'),
            new \DateTimeImmutable('+12 days') // 3 jours demandés
        );

        $solde = new class {
            public function getJoursRestants(): float
            {
                return 25.0;
            }
        };

        $this->creerService([], $solde)->validerNouvelleDemande($conge);
        $this->addToAssertionCount(1); // aucune exception levée = succès
    }

    public function testNeLeveRienSiAucunSoldeDefiniPourCeType(): void
    {
        // Type de congé sans décompte (ex. congé sans solde) : pas de blocage
        $conge = $this->creerConge(
            new \DateTimeImmutable('+10 days'),
            new \DateTimeImmutable('+12 days')
        );

        $this->creerService([], null)->validerNouvelleDemande($conge);
        $this->addToAssertionCount(1);
    }
}
