<?php

namespace App\Repository;

use App\Entity\RecipeImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RecipeImage|null find($id, $lockMode = null, $lockVersion = null)
 * @method RecipeImage|null findOneBy(array $criteria, array $orderBy = null)
 * @method RecipeImage[]    findAll()
 * @method RecipeImage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RecipeImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RecipeImage::class);
    }

    // Exemples de méthodes personnalisées que tu pourrais ajouter :
    // 
    // public function findByRecipeId($recipeId)
    // {
    //     return $this->createQueryBuilder('ri')
    //         ->andWhere('ri.recipe = :recipeId')
    //         ->setParameter('recipeId', $recipeId)
    //         ->orderBy('ri.id', 'ASC')
    //         ->getQuery()
    //         ->getResult();
    // }
}
