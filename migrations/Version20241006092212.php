<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241006092212 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE palanquee DROP FOREIGN KEY FK_7BC236BFEF1BA4');
        $this->addSql('ALTER TABLE sortie DROP FOREIGN KEY FK_3C3FD3F23D1873C');
        $this->addSql('ALTER TABLE sortie DROP FOREIGN KEY FK_3C3FD3F28993973C');
        $this->addSql('ALTER TABLE sortie DROP FOREIGN KEY FK_3C3FD3F2CFB8F3CA');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BCA234A5D3');
        $this->addSql('ALTER TABLE moniteur DROP FOREIGN KEY FK_B3EC8EBABF396750');
        $this->addSql('DROP TABLE moniteur');
//        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BCA234A5D3');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BCA234A5D3 FOREIGN KEY (moniteur_id) REFERENCES plongeur (id)');
//        $this->addSql('ALTER TABLE palanquee DROP FOREIGN KEY FK_7BC236BFEF1BA4');
        $this->addSql('ALTER TABLE palanquee ADD CONSTRAINT FK_7BC236BFEF1BA4 FOREIGN KEY (encadrant_id) REFERENCES plongeur (id)');
        $this->addSql('ALTER TABLE plongeur ADD niveau_moniteur VARCHAR(25) NOT NULL, ADD is_moniteur TINYINT(1) NOT NULL, DROP type');
//        $this->addSql('ALTER TABLE sortie DROP FOREIGN KEY FK_3C3FD3F23D1873C');
//        $this->addSql('ALTER TABLE sortie DROP FOREIGN KEY FK_3C3FD3F28993973C');
//        $this->addSql('ALTER TABLE sortie DROP FOREIGN KEY FK_3C3FD3F2CFB8F3CA');
        $this->addSql('ALTER TABLE sortie ADD CONSTRAINT FK_3C3FD3F23D1873C FOREIGN KEY (securite_surface_id) REFERENCES plongeur (id)');
        $this->addSql('ALTER TABLE sortie ADD CONSTRAINT FK_3C3FD3F28993973C FOREIGN KEY (pilote_bateau_id) REFERENCES plongeur (id)');
        $this->addSql('ALTER TABLE sortie ADD CONSTRAINT FK_3C3FD3F2CFB8F3CA FOREIGN KEY (directeur_plongee_id) REFERENCES plongeur (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE moniteur (id INT NOT NULL, niveau_moniteur VARCHAR(25) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE moniteur ADD CONSTRAINT FK_B3EC8EBABF396750 FOREIGN KEY (id) REFERENCES plongeur (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE plongeur ADD type VARCHAR(255) NOT NULL, DROP niveau_moniteur, DROP is_moniteur');
        $this->addSql('ALTER TABLE palanquee DROP FOREIGN KEY FK_7BC236BFEF1BA4');
        $this->addSql('ALTER TABLE palanquee ADD CONSTRAINT FK_7BC236BFEF1BA4 FOREIGN KEY (encadrant_id) REFERENCES moniteur (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE sortie DROP FOREIGN KEY FK_3C3FD3F2CFB8F3CA');
        $this->addSql('ALTER TABLE sortie DROP FOREIGN KEY FK_3C3FD3F23D1873C');
        $this->addSql('ALTER TABLE sortie DROP FOREIGN KEY FK_3C3FD3F28993973C');
        $this->addSql('ALTER TABLE sortie ADD CONSTRAINT FK_3C3FD3F2CFB8F3CA FOREIGN KEY (directeur_plongee_id) REFERENCES moniteur (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE sortie ADD CONSTRAINT FK_3C3FD3F23D1873C FOREIGN KEY (securite_surface_id) REFERENCES moniteur (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE sortie ADD CONSTRAINT FK_3C3FD3F28993973C FOREIGN KEY (pilote_bateau_id) REFERENCES moniteur (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BCA234A5D3');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BCA234A5D3 FOREIGN KEY (moniteur_id) REFERENCES moniteur (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
