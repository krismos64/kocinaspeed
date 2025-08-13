<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Optimisation des performances avec ajout d'indexes
 */
final class Version20241213120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout d\'indexes pour optimiser les performances des requêtes';
    }

    public function up(Schema $schema): void
    {
        // Index sur les colonnes fréquemment utilisées pour les requêtes
        $this->addSql('CREATE INDEX IDX_DA88B137989D9B62 ON recipe (slug)');
        $this->addSql('CREATE INDEX IDX_DA88B1379F75D7B0 ON recipe (category)');
        $this->addSql('CREATE INDEX IDX_DA88B137B03A8386 ON recipe (created_at)');
        $this->addSql('CREATE INDEX IDX_DA88B1375E237E06 ON recipe (name)');
        
        // Index sur les reviews pour les requêtes d'approbation
        $this->addSql('CREATE INDEX IDX_794381C64E5C48F8 ON review (approved)');
        $this->addSql('CREATE INDEX IDX_794381C6B03A8386 ON review (created_at)');
        
        // Index composé pour les recherches
        $this->addSql('CREATE INDEX IDX_SEARCH ON recipe (name, category)');
    }

    public function down(Schema $schema): void
    {
        // Suppression des indexes
        $this->addSql('DROP INDEX IDX_DA88B137989D9B62 ON recipe');
        $this->addSql('DROP INDEX IDX_DA88B1379F75D7B0 ON recipe');
        $this->addSql('DROP INDEX IDX_DA88B137B03A8386 ON recipe');
        $this->addSql('DROP INDEX IDX_DA88B1375E237E06 ON recipe');
        $this->addSql('DROP INDEX IDX_794381C64E5C48F8 ON review');
        $this->addSql('DROP INDEX IDX_794381C6B03A8386 ON review');
        $this->addSql('DROP INDEX IDX_SEARCH ON recipe');
    }
}