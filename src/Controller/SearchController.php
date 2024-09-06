<?php

namespace App\Controller;

use App\Form\SearchType;
use App\Repository\RecipeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'search')]
    public function search(Request $request, RecipeRepository $recipeRepository): Response
    {
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);

        $recipes = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
            $recipes = $recipeRepository->findByCriteria($criteria);
        }

        return $this->render('search/index.html.twig', [
            'form' => $form->createView(),
            'recipes' => $recipes,
        ]);
    }
}