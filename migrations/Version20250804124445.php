<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250804124445 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE dish (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, instructions LONGTEXT NOT NULL, preparation_time INT NOT NULL, cooking_time INT NOT NULL, difficulty INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dish_tag (dish_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_64FF4A98148EB0CB (dish_id), INDEX IDX_64FF4A98BAD26311 (tag_id), PRIMARY KEY(dish_id, tag_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ingredient (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, dish_id INT NOT NULL, quantity DOUBLE PRECISION NOT NULL, INDEX IDX_6BAF78704584665A (product_id), INDEX IDX_6BAF7870148EB0CB (dish_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, color VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE dish_tag ADD CONSTRAINT FK_64FF4A98148EB0CB FOREIGN KEY (dish_id) REFERENCES dish (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dish_tag ADD CONSTRAINT FK_64FF4A98BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ingredient ADD CONSTRAINT FK_6BAF78704584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE ingredient ADD CONSTRAINT FK_6BAF7870148EB0CB FOREIGN KEY (dish_id) REFERENCES dish (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dish_tag DROP FOREIGN KEY FK_64FF4A98148EB0CB');
        $this->addSql('ALTER TABLE dish_tag DROP FOREIGN KEY FK_64FF4A98BAD26311');
        $this->addSql('ALTER TABLE ingredient DROP FOREIGN KEY FK_6BAF78704584665A');
        $this->addSql('ALTER TABLE ingredient DROP FOREIGN KEY FK_6BAF7870148EB0CB');
        $this->addSql('DROP TABLE dish');
        $this->addSql('DROP TABLE dish_tag');
        $this->addSql('DROP TABLE ingredient');
        $this->addSql('DROP TABLE tag');
    }
}
