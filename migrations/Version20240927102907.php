<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240927102907 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE recipe_image (id INT AUTO_INCREMENT NOT NULL, recipe_id INT DEFAULT NULL, image_path VARCHAR(255) NOT NULL, INDEX IDX_D32ED04059D8A214 (recipe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE recipe_image ADD CONSTRAINT FK_D32ED04059D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id)');
        $this->addSql('ALTER TABLE recipe ADD ingredients LONGTEXT DEFAULT NULL, ADD cooking_time INT DEFAULT NULL, DROP subtitle, DROP image, CHANGE video video VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recipe_image DROP FOREIGN KEY FK_D32ED04059D8A214');
        $this->addSql('DROP TABLE recipe_image');
        $this->addSql('ALTER TABLE recipe ADD subtitle VARCHAR(255) NOT NULL, ADD image VARCHAR(255) NOT NULL, DROP ingredients, DROP cooking_time, CHANGE video video VARCHAR(255) NOT NULL');
    }
}
