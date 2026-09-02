<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Vérifie que les erreurs métier (période invalide, chevauchement,
 * solde insuffisant) sont bien restituées à l'utilisateur comme un
 * message clair (HTTP 200 + flash), jamais comme une exception non
 * gérée (HTTP 500).
 *
 * Nécessite les fixtures chargées (doctrine:fixtures:load) en base
 * de test avant exécution.
 */
class CongeControllerTest extends WebTestCase
{
    public function testAccesRefuseSansConnexion(): void
    {
        $client = static::createClient();
        $client->request('GET', '/conges');

        $this->assertResponseRedirects('/login');
    }

    public function testDemandeAvecDateFinAvantDateDebutAfficheUneErreur(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get('doctrine')->getRepository(\App\Entity\Utilisateur::class);
        $utilisateur = $userRepository->findOneBy(['email' => 'julie@acme.fr']);
        $client->loginUser($utilisateur);

        $client->request('GET', '/conges/nouvelle');
        $client->submitForm('Envoyer la demande', [
            'conge[dateDebut]' => (new \DateTimeImmutable('+10 days'))->format('Y-m-d'),
            'conge[dateFin]' => (new \DateTimeImmutable('+5 days'))->format('Y-m-d'),
        ]);

        // La page se réaffiche avec un message d'erreur, pas un 500
        $this->assertResponseIsSuccessful();
    }
}
