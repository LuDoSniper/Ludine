<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250820012159 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ingredient ADD owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE ingredient ADD CONSTRAINT FK_6BAF78707E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_6BAF78707E3C61F9 ON ingredient (owner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ingredient DROP FOREIGN KEY FK_6BAF78707E3C61F9');
        $this->addSql('DROP INDEX IDX_6BAF78707E3C61F9 ON ingredient');
        $this->addSql('ALTER TABLE ingredient DROP owner_id');
    }
}
