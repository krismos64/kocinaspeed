<?php

namespace App\Controller;

use App\Entity\Recipe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/recipe')]
class RecipeController extends AbstractController
{
    #[Route('/{id}', name: 'recipe_show', methods: ['GET'])]
    public function show(Recipe $recipe): Response
    {
        return $this->render('recipe/show.html.twig', [
            'recipe' => $recipe,
        ]);
    }
    #[Route('/', name: 'recipe_index', methods: ['GET'])]
    public function index(): Response
    {
        // Logique pour récupérer et afficher les recettes
        return $this->render('recipe/index.html.twig', [
            // Passer les recettes à la vue ici
        ]);
    }
}