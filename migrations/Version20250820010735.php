<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250820010735 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE config ADD owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE config ADD CONSTRAINT FK_D48A2F7C7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D48A2F7C7E3C61F9 ON config (owner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE config DROP FOREIGN KEY FK_D48A2F7C7E3C61F9');
        $this->addSql('DROP INDEX UNIQ_D48A2F7C7E3C61F9 ON config');
        $this->addSql('ALTER TABLE config DROP owner_id');
    }
}
