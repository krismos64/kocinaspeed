<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RecipeController extends AbstractController
{
    #[Route('/recipe/{slug}', name: 'app_recipe_details')]
    public function show(recipeRepository $recipeRepository, string $slug): Response
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
    public function index(RecipeRepository $recipeRepository): Response
    {
        $recipes = $recipeRepository->findAll();

        return $this->render('recipe/index.html.twig', [
            'recipes' => $recipes,
        ]);
    }



    #[Route('/add-recipe', name: 'create_recipe')]
    public function createRecipe(EntityManagerInterface $entityManager): Response
    {
        $recipe = new Recipe();
        $recipe->setName('');
        $recipe->setSlug('');
        $recipe->setSubtitle('');
        $recipe->setDescription('');
        $recipe->setImage('');
        $recipe->setVideo('');

        $entityManager->persist($recipe);
        $entityManager->flush();

        return new Response('Saved new recette with id ' . $recette->getId());
    }
}