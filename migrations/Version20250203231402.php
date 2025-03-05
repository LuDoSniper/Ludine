<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250203231402 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE stocked_product (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, container_id INT NOT NULL, arival_date DATE NOT NULL, expiration_date DATE NOT NULL, stackable TINYINT(1) NOT NULL, cool TINYINT(1) NOT NULL, floor INT NOT NULL, location INT NOT NULL, INDEX IDX_C2EA0AA64584665A (product_id), INDEX IDX_C2EA0AA6BC21F742 (container_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE stocked_product ADD CONSTRAINT FK_C2EA0AA64584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE stocked_product ADD CONSTRAINT FK_C2EA0AA6BC21F742 FOREIGN KEY (container_id) REFERENCES container (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stocked_product DROP FOREIGN KEY FK_C2EA0AA64584665A');
        $this->addSql('ALTER TABLE stocked_product DROP FOREIGN KEY FK_C2EA0AA6BC21F742');
        $this->addSql('DROP TABLE stocked_product');
    }
}
