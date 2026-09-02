<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260901081619 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE conge (id INT AUTO_INCREMENT NOT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, statut VARCHAR(20) NOT NULL, commentaire LONGTEXT DEFAULT NULL, utilisateur_id INT NOT NULL, type_conge_id INT NOT NULL, validateur_id INT DEFAULT NULL, INDEX IDX_2ED89348FB88E14F (utilisateur_id), INDEX IDX_2ED89348753BDA5 (type_conge_id), INDEX IDX_2ED89348E57AEF2F (validateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE conge_historique (id INT AUTO_INCREMENT NOT NULL, ancien_statut VARCHAR(20) NOT NULL, nouveau_statut VARCHAR(20) NOT NULL, date_action DATETIME NOT NULL, conge_id INT NOT NULL, utilisateur_id INT NOT NULL, INDEX IDX_8BC07D91CAAC9A59 (conge_id), INDEX IDX_8BC07D91FB88E14F (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE entreprise (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, siret VARCHAR(14) DEFAULT NULL, adresse VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE jour_ferie (id INT AUTO_INCREMENT NOT NULL, date DATE NOT NULL, libelle VARCHAR(255) NOT NULL, entreprise_id INT NOT NULL, INDEX IDX_122AB5A4AEAFEA (entreprise_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE service (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, entreprise_id INT NOT NULL, INDEX IDX_E19D9AD2A4AEAFEA (entreprise_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE solde_conge (id INT AUTO_INCREMENT NOT NULL, annee INT NOT NULL, jours_restants DOUBLE PRECISION NOT NULL, utilisateur_id INT NOT NULL, type_conge_id INT NOT NULL, INDEX IDX_EF1BB27FB88E14F (utilisateur_id), INDEX IDX_EF1BB27753BDA5 (type_conge_id), UNIQUE INDEX UNIQ_SOLDE_UTILISATEUR_TYPE_ANNEE (utilisateur_id, type_conge_id, annee), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE type_conge (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, jours_defaut INT DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `utilisateur` (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, date_entree DATE DEFAULT NULL, statut VARCHAR(20) NOT NULL, doit_changer_mot_de_passe TINYINT NOT NULL, UNIQUE INDEX UNIQ_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE utilisateur_service (id INT AUTO_INCREMENT NOT NULL, est_responsable TINYINT NOT NULL, utilisateur_id INT NOT NULL, service_id INT NOT NULL, INDEX IDX_9B966D40FB88E14F (utilisateur_id), INDEX IDX_9B966D40ED5CA9E6 (service_id), UNIQUE INDEX UNIQ_UTILISATEUR_SERVICE (utilisateur_id, service_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE conge ADD CONSTRAINT FK_2ED89348FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES `utilisateur` (id)');
        $this->addSql('ALTER TABLE conge ADD CONSTRAINT FK_2ED89348753BDA5 FOREIGN KEY (type_conge_id) REFERENCES type_conge (id)');
        $this->addSql('ALTER TABLE conge ADD CONSTRAINT FK_2ED89348E57AEF2F FOREIGN KEY (validateur_id) REFERENCES `utilisateur` (id)');
        $this->addSql('ALTER TABLE conge_historique ADD CONSTRAINT FK_8BC07D91CAAC9A59 FOREIGN KEY (conge_id) REFERENCES conge (id)');
        $this->addSql('ALTER TABLE conge_historique ADD CONSTRAINT FK_8BC07D91FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES `utilisateur` (id)');
        $this->addSql('ALTER TABLE jour_ferie ADD CONSTRAINT FK_122AB5A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD2A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE solde_conge ADD CONSTRAINT FK_EF1BB27FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES `utilisateur` (id)');
        $this->addSql('ALTER TABLE solde_conge ADD CONSTRAINT FK_EF1BB27753BDA5 FOREIGN KEY (type_conge_id) REFERENCES type_conge (id)');
        $this->addSql('ALTER TABLE utilisateur_service ADD CONSTRAINT FK_9B966D40FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES `utilisateur` (id)');
        $this->addSql('ALTER TABLE utilisateur_service ADD CONSTRAINT FK_9B966D40ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conge DROP FOREIGN KEY FK_2ED89348FB88E14F');
        $this->addSql('ALTER TABLE conge DROP FOREIGN KEY FK_2ED89348753BDA5');
        $this->addSql('ALTER TABLE conge DROP FOREIGN KEY FK_2ED89348E57AEF2F');
        $this->addSql('ALTER TABLE conge_historique DROP FOREIGN KEY FK_8BC07D91CAAC9A59');
        $this->addSql('ALTER TABLE conge_historique DROP FOREIGN KEY FK_8BC07D91FB88E14F');
        $this->addSql('ALTER TABLE jour_ferie DROP FOREIGN KEY FK_122AB5A4AEAFEA');
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD2A4AEAFEA');
        $this->addSql('ALTER TABLE solde_conge DROP FOREIGN KEY FK_EF1BB27FB88E14F');
        $this->addSql('ALTER TABLE solde_conge DROP FOREIGN KEY FK_EF1BB27753BDA5');
        $this->addSql('ALTER TABLE utilisateur_service DROP FOREIGN KEY FK_9B966D40FB88E14F');
        $this->addSql('ALTER TABLE utilisateur_service DROP FOREIGN KEY FK_9B966D40ED5CA9E6');
        $this->addSql('DROP TABLE conge');
        $this->addSql('DROP TABLE conge_historique');
        $this->addSql('DROP TABLE entreprise');
        $this->addSql('DROP TABLE jour_ferie');
        $this->addSql('DROP TABLE service');
        $this->addSql('DROP TABLE solde_conge');
        $this->addSql('DROP TABLE type_conge');
        $this->addSql('DROP TABLE `utilisateur`');
        $this->addSql('DROP TABLE utilisateur_service');
    }
}
