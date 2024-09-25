<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240925150444 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE review DROP images');
        $this->addSql('ALTER TABLE review_image DROP FOREIGN KEY FK_D6B328443E2E969B');
        $this->addSql('ALTER TABLE review_image CHANGE image_path image_path VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE review_image ADD CONSTRAINT FK_D6B328443E2E969B FOREIGN KEY (review_id) REFERENCES review (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE review ADD images LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE review_image DROP FOREIGN KEY FK_D6B328443E2E969B');
        $this->addSql('ALTER TABLE review_image CHANGE image_path image_path VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE review_image ADD CONSTRAINT FK_D6B328443E2E969B FOREIGN KEY (review_id) REFERENCES review (id)');
    }
}
