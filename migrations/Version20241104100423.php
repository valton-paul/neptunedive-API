<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241104100423 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE assignation_palanquee (id INT AUTO_INCREMENT NOT NULL, plongeur_id INT NOT NULL, palanquee_id INT NOT NULL, INDEX IDX_23D6492167E90C15 (plongeur_id), INDEX IDX_23D649215EBD6930 (palanquee_id), UNIQUE INDEX UNIQ_IDENTIFIER_PALANQUEE_PLONGEUR (palanquee_id, plongeur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE inscription_sortie (id INT AUTO_INCREMENT NOT NULL, sortie_id INT NOT NULL, plongeur_id INT NOT NULL, INDEX IDX_3400A78BCC72D953 (sortie_id), INDEX IDX_3400A78B67E90C15 (plongeur_id), UNIQUE INDEX UNIQ_IDENTIFIER_SORTIE_PLONGEUR (sortie_id, plongeur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE assignation_palanquee ADD CONSTRAINT FK_23D6492167E90C15 FOREIGN KEY (plongeur_id) REFERENCES plongeur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE assignation_palanquee ADD CONSTRAINT FK_23D649215EBD6930 FOREIGN KEY (palanquee_id) REFERENCES palanquee (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE inscription_sortie ADD CONSTRAINT FK_3400A78BCC72D953 FOREIGN KEY (sortie_id) REFERENCES sortie (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE inscription_sortie ADD CONSTRAINT FK_3400A78B67E90C15 FOREIGN KEY (plongeur_id) REFERENCES plongeur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sortie_plongeur DROP FOREIGN KEY FK_6DAB8AF767E90C15');
        $this->addSql('ALTER TABLE sortie_plongeur DROP FOREIGN KEY FK_6DAB8AF7CC72D953');
        $this->addSql('ALTER TABLE palanquee_plongeur DROP FOREIGN KEY FK_23B56DD95EBD6930');
        $this->addSql('ALTER TABLE palanquee_plongeur DROP FOREIGN KEY FK_23B56DD967E90C15');
        $this->addSql('DROP TABLE sortie_plongeur');
        $this->addSql('DROP TABLE palanquee_plongeur');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sortie_plongeur (sortie_id INT NOT NULL, plongeur_id INT NOT NULL, INDEX IDX_6DAB8AF767E90C15 (plongeur_id), INDEX IDX_6DAB8AF7CC72D953 (sortie_id), PRIMARY KEY(sortie_id, plongeur_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE palanquee_plongeur (palanquee_id INT NOT NULL, plongeur_id INT NOT NULL, INDEX IDX_23B56DD967E90C15 (plongeur_id), INDEX IDX_23B56DD95EBD6930 (palanquee_id), PRIMARY KEY(palanquee_id, plongeur_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE sortie_plongeur ADD CONSTRAINT FK_6DAB8AF767E90C15 FOREIGN KEY (plongeur_id) REFERENCES plongeur (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sortie_plongeur ADD CONSTRAINT FK_6DAB8AF7CC72D953 FOREIGN KEY (sortie_id) REFERENCES sortie (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE palanquee_plongeur ADD CONSTRAINT FK_23B56DD95EBD6930 FOREIGN KEY (palanquee_id) REFERENCES palanquee (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE palanquee_plongeur ADD CONSTRAINT FK_23B56DD967E90C15 FOREIGN KEY (plongeur_id) REFERENCES plongeur (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE assignation_palanquee DROP FOREIGN KEY FK_23D6492167E90C15');
        $this->addSql('ALTER TABLE assignation_palanquee DROP FOREIGN KEY FK_23D649215EBD6930');
        $this->addSql('ALTER TABLE inscription_sortie DROP FOREIGN KEY FK_3400A78BCC72D953');
        $this->addSql('ALTER TABLE inscription_sortie DROP FOREIGN KEY FK_3400A78B67E90C15');
        $this->addSql('DROP TABLE assignation_palanquee');
        $this->addSql('DROP TABLE inscription_sortie');
    }
}
