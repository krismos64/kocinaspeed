<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\CacheItem;

/**
 * Service d'optimisation des performances base de données
 */
class DatabaseOptimizationService
{
    private EntityManagerInterface $entityManager;
    private AdapterInterface $cache;

    public function __construct(EntityManagerInterface $entityManager, AdapterInterface $cache)
    {
        $this->entityManager = $entityManager;
        $this->cache = $cache;
    }

    /**
     * Cache une requête avec TTL
     */
    public function getCachedQuery(string $cacheKey, callable $queryCallback, int $ttl = 3600): mixed
    {
        $cacheItem = $this->cache->getItem($cacheKey);
        
        if (!$cacheItem->isHit()) {
            $result = $queryCallback();
            $cacheItem->set($result);
            $cacheItem->expiresAfter($ttl);
            $this->cache->save($cacheItem);
            
            return $result;
        }
        
        return $cacheItem->get();
    }

    /**
     * Optimise les requêtes de recettes pour la page d'accueil
     */
    public function getOptimizedHomeData(): array
    {
        return $this->getCachedQuery('home_recipes_data', function() {
            $connection = $this->entityManager->getConnection();
            
            // Requête native optimisée pour récupérer les données home
            $sql = '
                SELECT r.id, r.name, r.slug, r.category, r.created_at,
                       ri.image_path as first_image
                FROM recipe r
                LEFT JOIN (
                    SELECT recipe_id, image_path, 
                           ROW_NUMBER() OVER (PARTITION BY recipe_id ORDER BY id ASC) as rn
                    FROM recipe_image
                ) ri ON r.id = ri.recipe_id AND ri.rn = 1
                ORDER BY r.created_at DESC
                LIMIT 6
            ';
            
            return $connection->fetchAllAssociative($sql);
        }, 1800); // Cache 30 minutes
    }

    /**
     * Optimise la recherche avec cache
     */
    public function searchRecipes(string $query): array
    {
        $cacheKey = 'recipe_search_' . md5($query);
        
        return $this->getCachedQuery($cacheKey, function() use ($query) {
            $connection = $this->entityManager->getConnection();
            
            $sql = '
                SELECT r.id, r.name, r.slug, r.category, r.description,
                       ri.image_path as first_image
                FROM recipe r
                LEFT JOIN (
                    SELECT recipe_id, image_path, 
                           ROW_NUMBER() OVER (PARTITION BY recipe_id ORDER BY id ASC) as rn
                    FROM recipe_image
                ) ri ON r.id = ri.recipe_id AND ri.rn = 1
                WHERE r.name LIKE :query 
                   OR JSON_SEARCH(r.ingredients, "one", :query) IS NOT NULL
                ORDER BY 
                    CASE WHEN r.name LIKE :exact_query THEN 1 ELSE 2 END,
                    r.name ASC
                LIMIT 20
            ';
            
            $params = [
                'query' => '%' . $query . '%',
                'exact_query' => $query . '%'
            ];
            
            return $connection->fetchAllAssociative($sql, $params);
        }, 900); // Cache 15 minutes
    }

    /**
     * Clear cache pour une recette spécifique
     */
    public function clearRecipeCache(string $slug): void
    {
        $cacheKeys = [
            'home_recipes_data',
            'recipe_' . $slug,
            'all_recipes_with_images'
        ];

        foreach ($cacheKeys as $key) {
            $this->cache->delete($key);
        }
    }

    /**
     * Clear tout le cache de recherche
     */
    public function clearSearchCache(): void
    {
        // Pour Symfony cache, on peut utiliser les tags mais ici on clear les patterns courants
        $cacheKeys = ['home_recipes_data', 'all_recipes_with_images'];
        foreach ($cacheKeys as $key) {
            $this->cache->delete($key);
        }
    }

    /**
     * Obtient les statistiques de performance
     */
    public function getPerformanceStats(): array
    {
        return $this->getCachedQuery('db_performance_stats', function() {
            $connection = $this->entityManager->getConnection();
            
            $recipeCount = $connection->fetchOne('SELECT COUNT(*) FROM recipe');
            $reviewCount = $connection->fetchOne('SELECT COUNT(*) FROM review WHERE approved = 1');
            $imageCount = $connection->fetchOne('SELECT COUNT(*) FROM recipe_image');
            
            return [
                'recipes' => (int) $recipeCount,
                'approved_reviews' => (int) $reviewCount,
                'images' => (int) $imageCount,
                'categories' => count(\App\Entity\Recipe::CATEGORIES)
            ];
        }, 3600); // Cache 1 hour
    }
}