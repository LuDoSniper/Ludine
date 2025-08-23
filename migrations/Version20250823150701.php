<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250823150701 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE chat (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', name VARCHAR(255) DEFAULT NULL, INDEX IDX_659DF2AA7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chat_user (chat_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_2B0F4B081A9A7125 (chat_id), INDEX IDX_2B0F4B08A76ED395 (user_id), PRIMARY KEY(chat_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE config (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, selection_mode INT NOT NULL, select_lunch TINYINT(1) NOT NULL, select_diner TINYINT(1) NOT NULL, lunch_time TIME DEFAULT NULL, diner_time TIME DEFAULT NULL, max_difficulty INT NOT NULL, UNIQUE INDEX UNIQ_D48A2F7C7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE container (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, cool TINYINT(1) NOT NULL, nb_floor INT NOT NULL, ref VARCHAR(255) NOT NULL, floors LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_C7A2EC1B7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dashboard (id INT AUTO_INCREMENT NOT NULL, lunch_dish_id INT DEFAULT NULL, lunch_dish_doable_id INT DEFAULT NULL, diner_dish_id INT DEFAULT NULL, diner_dish_doable_id INT DEFAULT NULL, owner_id INT NOT NULL, date DATE NOT NULL, INDEX IDX_5C94FFF8FDF97CED (lunch_dish_id), INDEX IDX_5C94FFF8BF31BA1B (lunch_dish_doable_id), INDEX IDX_5C94FFF89A18B755 (diner_dish_id), INDEX IDX_5C94FFF864C1747E (diner_dish_doable_id), UNIQUE INDEX UNIQ_5C94FFF87E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dish (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, instructions LONGTEXT NOT NULL, preparation_time INT NOT NULL, cooking_time INT NOT NULL, difficulty INT NOT NULL, drop_rate DOUBLE PRECISION NOT NULL, INDEX IDX_957D8CB87E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dish_tag (dish_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_64FF4A98148EB0CB (dish_id), INDEX IDX_64FF4A98BAD26311 (tag_id), PRIMARY KEY(dish_id, tag_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ingredient (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, dish_id INT NOT NULL, owner_id INT NOT NULL, quantity DOUBLE PRECISION NOT NULL, INDEX IDX_6BAF78704584665A (product_id), INDEX IDX_6BAF7870148EB0CB (dish_id), INDEX IDX_6BAF78707E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, chat_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', content LONGTEXT NOT NULL, active TINYINT(1) NOT NULL, INDEX IDX_B6BD307FF675F31B (author_id), INDEX IDX_B6BD307F1A9A7125 (chat_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, INDEX IDX_D34A04AD7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE share (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, active TINYINT(1) NOT NULL, entities LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', valid TINYINT(1) NOT NULL, valid_members LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_EF069D5A7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE share_user (share_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_3054DBD02AE63FDB (share_id), INDEX IDX_3054DBD0A76ED395 (user_id), PRIMARY KEY(share_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stocked_product (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, container_id INT NOT NULL, owner_id INT NOT NULL, arrival_date DATE NOT NULL, expiration_date DATE NOT NULL, stackable TINYINT(1) NOT NULL, cool TINYINT(1) NOT NULL, floor INT NOT NULL, location INT NOT NULL, INDEX IDX_C2EA0AA64584665A (product_id), INDEX IDX_C2EA0AA6BC21F742 (container_id), INDEX IDX_C2EA0AA67E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, color VARCHAR(255) DEFAULT NULL, INDEX IDX_389B7837E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, username VARCHAR(180) NOT NULL, display_name VARCHAR(16) DEFAULT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, password_token VARCHAR(255) DEFAULT NULL, password_token_expiration DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username, email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE chat ADD CONSTRAINT FK_659DF2AA7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE chat_user ADD CONSTRAINT FK_2B0F4B081A9A7125 FOREIGN KEY (chat_id) REFERENCES chat (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chat_user ADD CONSTRAINT FK_2B0F4B08A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE config ADD CONSTRAINT FK_D48A2F7C7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE container ADD CONSTRAINT FK_C7A2EC1B7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE dashboard ADD CONSTRAINT FK_5C94FFF8FDF97CED FOREIGN KEY (lunch_dish_id) REFERENCES dish (id)');
        $this->addSql('ALTER TABLE dashboard ADD CONSTRAINT FK_5C94FFF8BF31BA1B FOREIGN KEY (lunch_dish_doable_id) REFERENCES dish (id)');
        $this->addSql('ALTER TABLE dashboard ADD CONSTRAINT FK_5C94FFF89A18B755 FOREIGN KEY (diner_dish_id) REFERENCES dish (id)');
        $this->addSql('ALTER TABLE dashboard ADD CONSTRAINT FK_5C94FFF864C1747E FOREIGN KEY (diner_dish_doable_id) REFERENCES dish (id)');
        $this->addSql('ALTER TABLE dashboard ADD CONSTRAINT FK_5C94FFF87E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE dish ADD CONSTRAINT FK_957D8CB87E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE dish_tag ADD CONSTRAINT FK_64FF4A98148EB0CB FOREIGN KEY (dish_id) REFERENCES dish (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dish_tag ADD CONSTRAINT FK_64FF4A98BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ingredient ADD CONSTRAINT FK_6BAF78704584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE ingredient ADD CONSTRAINT FK_6BAF7870148EB0CB FOREIGN KEY (dish_id) REFERENCES dish (id)');
        $this->addSql('ALTER TABLE ingredient ADD CONSTRAINT FK_6BAF78707E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F1A9A7125 FOREIGN KEY (chat_id) REFERENCES chat (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE share ADD CONSTRAINT FK_EF069D5A7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE share_user ADD CONSTRAINT FK_3054DBD02AE63FDB FOREIGN KEY (share_id) REFERENCES share (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE share_user ADD CONSTRAINT FK_3054DBD0A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE stocked_product ADD CONSTRAINT FK_C2EA0AA64584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE stocked_product ADD CONSTRAINT FK_C2EA0AA6BC21F742 FOREIGN KEY (container_id) REFERENCES container (id)');
        $this->addSql('ALTER TABLE stocked_product ADD CONSTRAINT FK_C2EA0AA67E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tag ADD CONSTRAINT FK_389B7837E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chat DROP FOREIGN KEY FK_659DF2AA7E3C61F9');
        $this->addSql('ALTER TABLE chat_user DROP FOREIGN KEY FK_2B0F4B081A9A7125');
        $this->addSql('ALTER TABLE chat_user DROP FOREIGN KEY FK_2B0F4B08A76ED395');
        $this->addSql('ALTER TABLE config DROP FOREIGN KEY FK_D48A2F7C7E3C61F9');
        $this->addSql('ALTER TABLE container DROP FOREIGN KEY FK_C7A2EC1B7E3C61F9');
        $this->addSql('ALTER TABLE dashboard DROP FOREIGN KEY FK_5C94FFF8FDF97CED');
        $this->addSql('ALTER TABLE dashboard DROP FOREIGN KEY FK_5C94FFF8BF31BA1B');
        $this->addSql('ALTER TABLE dashboard DROP FOREIGN KEY FK_5C94FFF89A18B755');
        $this->addSql('ALTER TABLE dashboard DROP FOREIGN KEY FK_5C94FFF864C1747E');
        $this->addSql('ALTER TABLE dashboard DROP FOREIGN KEY FK_5C94FFF87E3C61F9');
        $this->addSql('ALTER TABLE dish DROP FOREIGN KEY FK_957D8CB87E3C61F9');
        $this->addSql('ALTER TABLE dish_tag DROP FOREIGN KEY FK_64FF4A98148EB0CB');
        $this->addSql('ALTER TABLE dish_tag DROP FOREIGN KEY FK_64FF4A98BAD26311');
        $this->addSql('ALTER TABLE ingredient DROP FOREIGN KEY FK_6BAF78704584665A');
        $this->addSql('ALTER TABLE ingredient DROP FOREIGN KEY FK_6BAF7870148EB0CB');
        $this->addSql('ALTER TABLE ingredient DROP FOREIGN KEY FK_6BAF78707E3C61F9');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF675F31B');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F1A9A7125');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD7E3C61F9');
        $this->addSql('ALTER TABLE share DROP FOREIGN KEY FK_EF069D5A7E3C61F9');
        $this->addSql('ALTER TABLE share_user DROP FOREIGN KEY FK_3054DBD02AE63FDB');
        $this->addSql('ALTER TABLE share_user DROP FOREIGN KEY FK_3054DBD0A76ED395');
        $this->addSql('ALTER TABLE stocked_product DROP FOREIGN KEY FK_C2EA0AA64584665A');
        $this->addSql('ALTER TABLE stocked_product DROP FOREIGN KEY FK_C2EA0AA6BC21F742');
        $this->addSql('ALTER TABLE stocked_product DROP FOREIGN KEY FK_C2EA0AA67E3C61F9');
        $this->addSql('ALTER TABLE tag DROP FOREIGN KEY FK_389B7837E3C61F9');
        $this->addSql('DROP TABLE chat');
        $this->addSql('DROP TABLE chat_user');
        $this->addSql('DROP TABLE config');
        $this->addSql('DROP TABLE container');
        $this->addSql('DROP TABLE dashboard');
        $this->addSql('DROP TABLE dish');
        $this->addSql('DROP TABLE dish_tag');
        $this->addSql('DROP TABLE ingredient');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE share');
        $this->addSql('DROP TABLE share_user');
        $this->addSql('DROP TABLE stocked_product');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE user');
    }
}
