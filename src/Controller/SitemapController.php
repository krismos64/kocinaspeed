<?php

namespace App\Controller;

use App\Repository\RecipeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SitemapController extends AbstractController
{
    #[Route('/sitemap.xml', name: 'app_sitemap', methods: ['GET'])]
    public function index(RecipeRepository $recipeRepository): Response
    {
        // Récupérer toutes les recettes pour le sitemap
        $recipes = $recipeRepository->findAll();
        
        // URLs statiques importantes
        $staticUrls = [
            [
                'loc' => $this->generateUrl('app_home'),
                'changefreq' => 'daily',
                'priority' => '1.0',
                'lastmod' => new \DateTime('now')
            ],
            [
                'loc' => $this->generateUrl('app_recipe_index'),
                'changefreq' => 'daily', 
                'priority' => '0.9',
                'lastmod' => new \DateTime('now')
            ],
            [
                'loc' => $this->generateUrl('app_contact'),
                'changefreq' => 'monthly',
                'priority' => '0.7',
                'lastmod' => new \DateTime('now')
            ],
            [
                'loc' => $this->generateUrl('app_mentions_legales'),
                'changefreq' => 'yearly',
                'priority' => '0.3',
                'lastmod' => new \DateTime('now')
            ]
        ];

        // URLs des recettes dynamiques
        $recipeUrls = [];
        foreach ($recipes as $recipe) {
            $recipeUrls[] = [
                'loc' => $this->generateUrl('app_recipe_details', ['slug' => $recipe->getSlug()]),
                'changefreq' => 'weekly',
                'priority' => '0.8',
                'lastmod' => $recipe->getUpdatedAt() ?: $recipe->getCreatedAt() ?: new \DateTime('now'),
                'images' => $recipe->getImages()->toArray()
            ];
        }
        
        // URLs par catégories
        $categoryUrls = [];
        $categories = \App\Entity\Recipe::CATEGORIES;
        foreach ($categories as $categoryKey => $categoryLabel) {
            $categoryUrls[] = [
                'loc' => $this->generateUrl('app_recipe_list', ['category' => $categoryKey]),
                'changefreq' => 'daily',
                'priority' => '0.8',
                'lastmod' => new \DateTime('now')
            ];
        }

        $response = new Response(
            $this->renderView('sitemap/sitemap.xml.twig', [
                'staticUrls' => $staticUrls,
                'recipeUrls' => $recipeUrls,
                'categoryUrls' => $categoryUrls,
                'hostname' => $this->getParameter('app.hostname') ?? $_SERVER['HTTP_HOST'] ?? 'localhost'
            ])
        );
        
        $response->headers->set('Content-Type', 'application/xml');
        $response->headers->set('Cache-Control', 'public, max-age=3600'); // Cache 1 heure
        
        return $response;
    }

    #[Route('/sitemap-images.xml', name: 'app_sitemap_images', methods: ['GET'])]
    public function images(RecipeRepository $recipeRepository): Response
    {
        $recipes = $recipeRepository->findAllWithImages();
        
        $response = new Response(
            $this->renderView('sitemap/sitemap-images.xml.twig', [
                'recipes' => $recipes,
                'hostname' => $this->getParameter('app.hostname') ?? $_SERVER['HTTP_HOST'] ?? 'localhost'
            ])
        );
        
        $response->headers->set('Content-Type', 'application/xml');
        $response->headers->set('Cache-Control', 'public, max-age=7200'); // Cache 2 heures
        
        return $response;
    }

    #[Route('/robots.txt', name: 'app_robots', methods: ['GET'])]
    public function robots(): Response
    {
        $hostname = $this->getParameter('app.hostname') ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        $response = new Response(
            $this->renderView('sitemap/robots.txt.twig', [
                'hostname' => $hostname,
                'is_production' => $_SERVER['APP_ENV'] === 'prod'
            ])
        );
        
        $response->headers->set('Content-Type', 'text/plain');
        $response->headers->set('Cache-Control', 'public, max-age=86400'); // Cache 24 heures
        
        return $response;
    }
}