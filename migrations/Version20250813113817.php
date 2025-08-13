<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250813113817 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recipe CHANGE category category VARCHAR(255) DEFAULT \'PLATS\' NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DA88B137989D9B62 ON recipe (slug)');
        $this->addSql('ALTER TABLE recipe RENAME INDEX idx_da88b137989d9b62 TO idx_recipe_slug');
        $this->addSql('ALTER TABLE recipe RENAME INDEX idx_da88b1379f75d7b0 TO idx_recipe_category');
        $this->addSql('ALTER TABLE recipe RENAME INDEX idx_da88b137b03a8386 TO idx_recipe_created_at');
        $this->addSql('ALTER TABLE recipe RENAME INDEX idx_da88b1375e237e06 TO idx_recipe_name');
        $this->addSql('ALTER TABLE recipe RENAME INDEX idx_search TO idx_recipe_search');
        $this->addSql('ALTER TABLE review RENAME INDEX idx_794381c64e5c48f8 TO idx_review_approved');
        $this->addSql('ALTER TABLE review RENAME INDEX idx_794381c6b03a8386 TO idx_review_created_at');
        $this->addSql('ALTER TABLE review RENAME INDEX idx_794381c659d8a214 TO idx_review_recipe');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE review RENAME INDEX idx_review_approved TO IDX_794381C64E5C48F8');
        $this->addSql('ALTER TABLE review RENAME INDEX idx_review_recipe TO IDX_794381C659D8A214');
        $this->addSql('ALTER TABLE review RENAME INDEX idx_review_created_at TO IDX_794381C6B03A8386');
        $this->addSql('DROP INDEX UNIQ_DA88B137989D9B62 ON recipe');
        $this->addSql('ALTER TABLE recipe CHANGE category category VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE recipe RENAME INDEX idx_recipe_name TO IDX_DA88B1375E237E06');
        $this->addSql('ALTER TABLE recipe RENAME INDEX idx_recipe_slug TO IDX_DA88B137989D9B62');
        $this->addSql('ALTER TABLE recipe RENAME INDEX idx_recipe_category TO IDX_DA88B1379F75D7B0');
        $this->addSql('ALTER TABLE recipe RENAME INDEX idx_recipe_created_at TO IDX_DA88B137B03A8386');
        $this->addSql('ALTER TABLE recipe RENAME INDEX idx_recipe_search TO IDX_SEARCH');
    }
}
