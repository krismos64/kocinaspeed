<?php

namespace App\Repository;

use App\Entity\Recipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recipe>
 */
class RecipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recipe::class);
    }

    /**
     * Retourne une recette basée sur le slug fourni.
     * 
     * @param string $slug
     * @return Recipe|null
     */
    public function findOneBySlug(string $slug): ?Recipe
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }
    /**
     * Recherche optimisée avec JOIN sur les images pour éviter le N+1
     */
    public function findBySearchQuery(string $query)
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.images', 'ri')
            ->addSelect('ri')
            ->where('r.name LIKE :query OR r.ingredients LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les dernières recettes avec images - optimisé pour la page d'accueil
     */
    public function findLatestWithImages(int $limit = 6): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.images', 'ri')
            ->addSelect('ri')
            ->orderBy('r.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve toutes les recettes par catégorie avec images (pour navigation)
     */
    public function findAllWithImages(): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.images', 'ri')
            ->addSelect('ri')
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve une recette par slug avec toutes ses relations
     */
    public function findOneBySlugWithRelations(string $slug): ?Recipe
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.images', 'ri')
            ->leftJoin('r.reviews', 'rev')
            ->leftJoin('rev.images', 'revi')
            ->addSelect('ri', 'rev', 'revi')
            ->andWhere('r.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Query builder optimisé pour pagination avec images
     */
    public function createOptimizedQueryBuilder(string $category = 'all')
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.images', 'ri')
            ->addSelect('ri')
            ->orderBy('r.name', 'ASC');

        if ($category !== 'all') {
            $qb->andWhere('r.category = :category')
                ->setParameter('category', $category);
        }

        return $qb;
    }
}
