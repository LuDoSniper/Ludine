<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250820005353 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stocked_product ADD owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE stocked_product ADD CONSTRAINT FK_C2EA0AA67E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_C2EA0AA67E3C61F9 ON stocked_product (owner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stocked_product DROP FOREIGN KEY FK_C2EA0AA67E3C61F9');
        $this->addSql('DROP INDEX IDX_C2EA0AA67E3C61F9 ON stocked_product');
        $this->addSql('ALTER TABLE stocked_product DROP owner_id');
    }
}
