<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Service de gestion centralisé du cache pour KocinaSpeed
 */
class CacheService
{
    private CacheInterface $recipesCache;
    private CacheInterface $searchCache;
    private CacheInterface $staticCache;
    private CacheInterface $imagesCache;

    public function __construct(
        CacheInterface $recipesCache,
        CacheInterface $searchCache, 
        CacheInterface $staticCache,
        CacheInterface $imagesCache
    ) {
        $this->recipesCache = $recipesCache;
        $this->searchCache = $searchCache;
        $this->staticCache = $staticCache;
        $this->imagesCache = $imagesCache;
    }

    /**
     * Cache les données de la page d'accueil
     */
    public function getHomeData(callable $callback): array
    {
        return $this->recipesCache->get('home_recipes_data', function (ItemInterface $item) use ($callback) {
            $item->expiresAfter(1800); // 30 minutes
            return $callback();
        });
    }

    /**
     * Cache les données de recherche
     */
    public function getSearchResults(string $query, callable $callback): array
    {
        $cacheKey = 'search_' . md5(mb_strtolower($query));
        
        return $this->searchCache->get($cacheKey, function (ItemInterface $item) use ($callback) {
            $item->expiresAfter(900); // 15 minutes
            return $callback();
        });
    }

    /**
     * Cache les détails d'une recette
     */
    public function getRecipeData(string $slug, callable $callback): mixed
    {
        $cacheKey = 'recipe_' . $slug;
        
        return $this->recipesCache->get($cacheKey, function (ItemInterface $item) use ($callback) {
            $item->expiresAfter(3600); // 1 heure
            return $callback();
        });
    }

    /**
     * Cache les données de catégories
     */
    public function getCategoryData(string $category, int $page, callable $callback): array
    {
        $cacheKey = 'category_' . $category . '_page_' . $page;
        
        return $this->staticCache->get($cacheKey, function (ItemInterface $item) use ($callback) {
            $item->expiresAfter(2700); // 45 minutes
            return $callback();
        });
    }

    /**
     * Cache les statistiques globales
     */
    public function getGlobalStats(callable $callback): array
    {
        return $this->staticCache->get('global_statistics', function (ItemInterface $item) use ($callback) {
            $item->expiresAfter(3600); // 1 heure
            return $callback();
        });
    }

    /**
     * Cache les données d'images optimisées
     */
    public function getImageData(string $imagePath, callable $callback): array
    {
        $cacheKey = 'image_' . md5($imagePath);
        
        return $this->imagesCache->get($cacheKey, function (ItemInterface $item) use ($callback) {
            $item->expiresAfter(86400); // 24 heures
            return $callback();
        });
    }

    /**
     * Invalide le cache pour une recette spécifique
     */
    public function invalidateRecipe(string $slug): void
    {
        $keys = [
            'recipe_' . $slug,
            'home_recipes_data'
        ];

        foreach ($keys as $key) {
            $this->recipesCache->delete($key);
        }
        
        // Invalider aussi le cache statique car il peut contenir des données de catégorie
        $this->staticCache->clear();
    }

    /**
     * Invalide tout le cache de recherche
     */
    public function invalidateSearch(): void
    {
        $this->searchCache->clear();
    }

    /**
     * Invalide tout le cache de recettes
     */
    public function invalidateAllRecipes(): void
    {
        $this->recipesCache->clear();
        $this->staticCache->clear(); // Les catégories aussi
    }

    /**
     * Chauffe le cache pour les données critiques
     */
    public function warmUpCriticalData(callable $homeDataCallback, callable $statsCallback): void
    {
        // Pré-charger les données d'accueil
        $this->getHomeData($homeDataCallback);
        
        // Pré-charger les statistiques globales
        $this->getGlobalStats($statsCallback);
    }

    /**
     * Nettoie tous les caches (utile pour maintenance)
     */
    public function clearAllCache(): void
    {
        $this->recipesCache->clear();
        $this->searchCache->clear();
        $this->staticCache->clear();
        $this->imagesCache->clear();
    }

    /**
     * Obtient la taille du cache (estimation)
     */
    public function getCacheStats(): array
    {
        return [
            'recipes_cache_enabled' => true,
            'search_cache_enabled' => true,
            'static_cache_enabled' => true,
            'images_cache_enabled' => true,
            'cache_prefix' => 'kocinaspeed',
        ];
    }
}