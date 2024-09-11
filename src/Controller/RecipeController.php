<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RecipeController extends AbstractController
{
    #[Route('/recipe/{slug}', name: 'app_recipe_details')]
    public function show(RecipeRepository $recipeRepository, string $slug): Response
    {
        $recipe = $recipeRepository->findOneBySlug($slug);

        if (!$recipe) {
            throw $this->createNotFoundException('No recipe found for slug ' . $slug);
        }

        return $this->render('recipe/details.html.twig', [
            'recipe' => $recipe,
        ]);
    }

    #[Route('/recipes', name: 'app_recipe_index')]
    public function index(RecipeRepository $recipeRepository, Request $request): Response
    {
        $category = $request->query->get('category');
        if ($category) {
            $recipes = $recipeRepository->findBy(['category' => $category]);
        } else {
            $recipes = $recipeRepository->findAll();
        }

        return $this->render('recipe/index.html.twig', [
            'recipes' => $recipes,
        ]);
    }

    #[Route('/add-recipe', name: 'create_recipe')]
    public function createRecipe(EntityManagerInterface $entityManager): Response
    {
        $recipe = new Recipe();
        $recipe->setName('New Recipe');
        $recipe->setSlug('new-recipe');
        $recipe->setSubtitle('This is a subtitle');
        $recipe->setDescription('This is a description of the recipe.');
        $recipe->setImage('https://via.placeholder.com/300x160');
        $recipe->setVideo('https://example.com/video.mp4');
        $recipe->setRating(4.5);
        $recipe->setReviews(120);
        $recipe->setCategory('Plats'); 
        $entityManager->persist($recipe);
        $entityManager->flush();

        return new Response('Saved new recipe with id ' . $recipe->getId());
    }

    #[Route('/search', name: 'app_recipe_search')]
    public function search(RecipeRepository $recipeRepository, Request $request): Response
    {
        // Récupérer la requête de recherche depuis le formulaire
        $query = $request->query->get('query');
    
        if ($query) {
            // Rechercher les recettes par nom 
            $recipes = $recipeRepository->createQueryBuilder('r')
                ->where('r.name LIKE :query')
                ->setParameter('query', '%' . $query . '%')
                ->getQuery()
                ->getResult();
        } else {
            $recipes = [];
        }
    
        return $this->render('recipe/search_results.html.twig', [
            'recipes' => $recipes,
            'query' => $query
        ]);
    }
}