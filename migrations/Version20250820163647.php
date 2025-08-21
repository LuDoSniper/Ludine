<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250820163647 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dashboard ADD owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE dashboard ADD CONSTRAINT FK_5C94FFF87E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5C94FFF87E3C61F9 ON dashboard (owner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dashboard DROP FOREIGN KEY FK_5C94FFF87E3C61F9');
        $this->addSql('DROP INDEX UNIQ_5C94FFF87E3C61F9 ON dashboard');
        $this->addSql('ALTER TABLE dashboard DROP owner_id');
    }
}
