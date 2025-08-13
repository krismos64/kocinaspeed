<?php

namespace App\Command;

use App\Service\CacheService;
use App\Repository\RecipeRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:cache:warmup',
    description: 'Réchauffe le cache avec les données critiques de KocinaSpeed'
)]
class CacheWarmupCommand extends Command
{
    private CacheService $cacheService;
    private RecipeRepository $recipeRepository;

    public function __construct(CacheService $cacheService, RecipeRepository $recipeRepository)
    {
        $this->cacheService = $cacheService;
        $this->recipeRepository = $recipeRepository;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('🔥 Réchauffage du cache KocinaSpeed');

        // Nettoyer d'abord le cache existant
        $io->section('🧹 Nettoyage du cache existant');
        $this->cacheService->clearAllCache();
        $io->success('Cache nettoyé');

        // Réchauffer les données de la page d'accueil
        $io->section('🏠 Réchauffage des données d\'accueil');
        $this->cacheService->getHomeData(function() {
            return [
                'latestRecipes' => $this->recipeRepository->findLatestWithImages(6),
                'allRecipes' => $this->recipeRepository->findAllWithImages()
            ];
        });
        $io->success('Données d\'accueil mise en cache');

        // Réchauffer les statistiques globales
        $io->section('📊 Réchauffage des statistiques globales');
        $this->cacheService->getGlobalStats(function() {
            $recipeCount = $this->recipeRepository->count([]);
            return [
                'total_recipes' => $recipeCount,
                'categories' => count(\App\Entity\Recipe::CATEGORIES),
                'last_updated' => new \DateTime()
            ];
        });
        $io->success('Statistiques globales mise en cache');

        // Réchauffer le cache des catégories principales
        $io->section('📂 Réchauffage des données de catégories');
        $categories = ['all', 'DESSERTS', 'PLATS', 'APERITIFS'];
        
        foreach ($categories as $category) {
            $this->cacheService->getCategoryData($category, 1, function() use ($category) {
                $qb = $this->recipeRepository->createOptimizedQueryBuilder($category);
                return $qb->setMaxResults(9)->getQuery()->getResult();
            });
            $io->writeln("✅ Catégorie '{$category}' mise en cache");
        }
        $io->success('Toutes les catégories principales mises en cache');

        // Réchauffer les recettes populaires
        $io->section('⭐ Réchauffage des recettes populaires');
        $popularRecipes = $this->recipeRepository->findBy([], ['createdAt' => 'DESC'], 10);
        
        foreach ($popularRecipes as $recipe) {
            $this->cacheService->getRecipeData($recipe->getSlug(), function() use ($recipe) {
                return $this->recipeRepository->findOneBySlugWithRelations($recipe->getSlug());
            });
            $io->writeln("✅ Recette '{$recipe->getName()}' mise en cache");
        }
        $io->success('Recettes populaires mises en cache');

        // Afficher les statistiques finales
        $io->section('📈 Statistiques du cache');
        $stats = $this->cacheService->getCacheStats();
        $io->table(
            ['Type de Cache', 'Status'],
            [
                ['Recettes', $stats['recipes_cache_enabled'] ? '✅ Activé' : '❌ Désactivé'],
                ['Recherche', $stats['search_cache_enabled'] ? '✅ Activé' : '❌ Désactivé'],
                ['Statique', $stats['static_cache_enabled'] ? '✅ Activé' : '❌ Désactivé'],
                ['Images', $stats['images_cache_enabled'] ? '✅ Activé' : '❌ Désactivé'],
            ]
        );

        $io->success('🚀 Cache réchauffé avec succès ! L\'application devrait maintenant être plus rapide.');
        
        return Command::SUCCESS;
    }
}