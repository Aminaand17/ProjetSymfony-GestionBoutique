<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241129122838 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE dette_article (dette_id INT NOT NULL, article_id INT NOT NULL, PRIMARY KEY(dette_id, article_id))');
        $this->addSql('CREATE INDEX IDX_C5321D58E11400A1 ON dette_article (dette_id)');
        $this->addSql('CREATE INDEX IDX_C5321D587294869C ON dette_article (article_id)');
        $this->addSql('ALTER TABLE dette_article ADD CONSTRAINT FK_C5321D58E11400A1 FOREIGN KEY (dette_id) REFERENCES dette (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dette_article ADD CONSTRAINT FK_C5321D587294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE article ALTER prix TYPE NUMERIC(10, 2)');
        $this->addSql('ALTER TABLE dette ADD montant_restant NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE dette ALTER montant TYPE NUMERIC(10, 2)');
        $this->addSql('ALTER TABLE dette ALTER montant_verser TYPE NUMERIC(10, 2)');
        $this->addSql('ALTER TABLE dette ALTER montant_verser DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE dette_article DROP CONSTRAINT FK_C5321D58E11400A1');
        $this->addSql('ALTER TABLE dette_article DROP CONSTRAINT FK_C5321D587294869C');
        $this->addSql('DROP TABLE dette_article');
        $this->addSql('ALTER TABLE dette DROP montant_restant');
        $this->addSql('ALTER TABLE dette ALTER montant TYPE DOUBLE PRECISION');
        $this->addSql('ALTER TABLE dette ALTER montant_verser TYPE DOUBLE PRECISION');
        $this->addSql('ALTER TABLE dette ALTER montant_verser SET NOT NULL');
        $this->addSql('ALTER TABLE article ALTER prix TYPE DOUBLE PRECISION');
    }
}
