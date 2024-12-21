<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241210164228 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE demande (id SERIAL NOT NULL, client_id INT NOT NULL, montant INT NOT NULL, montant_restant INT NOT NULL, date_demande TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, statut VARCHAR(20) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2694D7A519EB6921 ON demande (client_id)');
        $this->addSql('COMMENT ON COLUMN demande.date_demande IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE demande ADD CONSTRAINT FK_2694D7A519EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dette ADD demande_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE dette ADD CONSTRAINT FK_831BC80880E95E18 FOREIGN KEY (demande_id) REFERENCES demande (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_831BC80880E95E18 ON dette (demande_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE dette DROP CONSTRAINT FK_831BC80880E95E18');
        $this->addSql('ALTER TABLE demande DROP CONSTRAINT FK_2694D7A519EB6921');
        $this->addSql('DROP TABLE demande');
        $this->addSql('DROP INDEX IDX_831BC80880E95E18');
        $this->addSql('ALTER TABLE dette DROP demande_id');
    }
}
