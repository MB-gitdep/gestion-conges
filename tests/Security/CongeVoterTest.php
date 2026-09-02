<?php

namespace App\Tests\Security;

use App\Entity\Conge;
use App\Entity\Service;
use App\Entity\Utilisateur;
use App\Entity\UtilisateurService;
use App\Security\CongeVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Vérifie chaque cas de figure du CongeVoter : demandeur, responsable
 * du bon service, responsable d'un autre service, RH, et le cas
 * interdit où un demandeur tente de valider sa propre demande.
 */
class CongeVoterTest extends TestCase
{
    private function affecter(Utilisateur $utilisateur, Service $service, bool $estResponsable): void
    {
        $affectation = new UtilisateurService();
        $affectation->setUtilisateur($utilisateur)->setService($service)->setEstResponsable($estResponsable);
        $utilisateur->addUtilisateurService($affectation);
    }

    private function token(Utilisateur $utilisateur): TokenInterface
    {
        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($utilisateur);

        return $token;
    }

    public function testLeDemandeurNePeutPasValiderSaPropreDemande(): void
    {
        $demandeur = new Utilisateur();
        $conge = (new Conge())->setUtilisateur($demandeur);

        $voter = new CongeVoter();
        $resultat = $voter->vote($this->token($demandeur), $conge, [CongeVoter::VALIDER]);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $resultat);
    }

    public function testLeDemandeurPeutConsulterSaPropreDemande(): void
    {
        $demandeur = new Utilisateur();
        $conge = (new Conge())->setUtilisateur($demandeur);

        $voter = new CongeVoter();
        $resultat = $voter->vote($this->token($demandeur), $conge, [CongeVoter::VOIR]);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $resultat);
    }

    public function testLeResponsableDuBonServicePeutValider(): void
    {
        $service = new Service();
        $demandeur = new Utilisateur();
        $responsable = new Utilisateur();

        $this->affecter($demandeur, $service, false);
        $this->affecter($responsable, $service, true);

        $conge = (new Conge())->setUtilisateur($demandeur);

        $voter = new CongeVoter();
        $resultat = $voter->vote($this->token($responsable), $conge, [CongeVoter::VALIDER]);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $resultat);
    }

    public function testUnResponsableDunAutreServiceNePeutPasValider(): void
    {
        $serviceA = new Service();
        $serviceB = new Service();
        $demandeur = new Utilisateur();
        $responsableAutreService = new Utilisateur();

        $this->affecter($demandeur, $serviceA, false);
        $this->affecter($responsableAutreService, $serviceB, true);

        $conge = (new Conge())->setUtilisateur($demandeur);

        $voter = new CongeVoter();
        $resultat = $voter->vote($this->token($responsableAutreService), $conge, [CongeVoter::VALIDER]);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $resultat);
    }

    public function testUnRhPeutToujoursValiderQuelQueSoitLeService(): void
    {
        $demandeur = new Utilisateur();
        $rh = new Utilisateur();
        $rh->setRoles(['ROLE_RH']);

        $conge = (new Conge())->setUtilisateur($demandeur);

        $voter = new CongeVoter();
        $resultat = $voter->vote($this->token($rh), $conge, [CongeVoter::VALIDER]);

        $this->assertSame(VoterInterface::ACCESS_GRANTED, $resultat);
    }

    public function testUnUtilisateurNonConnecteEstRefuse(): void
    {
        $demandeur = new Utilisateur();
        $conge = (new Conge())->setUtilisateur($demandeur);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn(null);

        $voter = new CongeVoter();
        $resultat = $voter->vote($token, $conge, [CongeVoter::VALIDER]);

        $this->assertSame(VoterInterface::ACCESS_DENIED, $resultat);
    }
}
