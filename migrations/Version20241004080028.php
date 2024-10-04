<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241004080028 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bateau ADD nb_places INT NOT NULL');
        $this->addSql('ALTER TABLE palanquee CHANGE encadrant_id encadrant_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE plongeur ADD niveau_plongee_encadre INT DEFAULT NULL, CHANGE niveau_plongee niveau_plongee INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sortie ADD bateau_id INT DEFAULT NULL, ADD pilote_bateau_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sortie ADD CONSTRAINT FK_3C3FD3F2A9706509 FOREIGN KEY (bateau_id) REFERENCES bateau (id)');
        $this->addSql('ALTER TABLE sortie ADD CONSTRAINT FK_3C3FD3F28993973C FOREIGN KEY (pilote_bateau_id) REFERENCES moniteur (id)');
        $this->addSql('CREATE INDEX IDX_3C3FD3F2A9706509 ON sortie (bateau_id)');
        $this->addSql('CREATE INDEX IDX_3C3FD3F28993973C ON sortie (pilote_bateau_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE palanquee CHANGE encadrant_id encadrant_id INT NOT NULL');
        $this->addSql('ALTER TABLE sortie DROP FOREIGN KEY FK_3C3FD3F2A9706509');
        $this->addSql('ALTER TABLE sortie DROP FOREIGN KEY FK_3C3FD3F28993973C');
        $this->addSql('DROP INDEX IDX_3C3FD3F2A9706509 ON sortie');
        $this->addSql('DROP INDEX IDX_3C3FD3F28993973C ON sortie');
        $this->addSql('ALTER TABLE sortie DROP bateau_id, DROP pilote_bateau_id');
        $this->addSql('ALTER TABLE plongeur DROP niveau_plongee_encadre, CHANGE niveau_plongee niveau_plongee VARCHAR(25) NOT NULL');
        $this->addSql('ALTER TABLE bateau DROP nb_places');
    }
}
