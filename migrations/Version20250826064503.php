<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250826064503 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE container CHANGE ref ref VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE dish CHANGE instructions instructions LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE share CHANGE entities entities LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE tag CHANGE color color VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE share CHANGE entities entities LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE dish CHANGE instructions instructions LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE container CHANGE ref ref VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE tag CHANGE color color VARCHAR(255) DEFAULT NULL');
    }
}
