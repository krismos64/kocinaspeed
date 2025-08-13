<?php

namespace App\Service;

use App\Entity\Recipe;
use Symfony\Component\HttpFoundation\Request;

class SeoOptimizationService
{
    /**
     * Génère des alt texts optimisés pour les images de recettes
     */
    public function generateImageAltText(Recipe $recipe, int $imageIndex = 0): string
    {
        $baseAlt = "Photo de la recette {$recipe->getName()}";
        
        if ($imageIndex > 0) {
            $baseAlt .= " - Image " . ($imageIndex + 1);
        }
        
        $category = $recipe->getCategory() ? " - {$recipe->getCategory()}" : "";
        $cookingTime = $recipe->getCookingTime() ? " - Préparation {$recipe->getCookingTime()} min" : " - Recette rapide";
        
        return $baseAlt . $category . $cookingTime . " | KocinaSpeed";
    }

    /**
     * Génère des descriptions optimisées pour les LLM/IA
     */
    public function generateAiOptimizedDescription(Recipe $recipe): string
    {
        $description = "Recette de {$recipe->getName()}";
        
        if ($recipe->getCategory()) {
            $description .= " ({$recipe->getCategory()})";
        }
        
        if ($recipe->getCookingTime()) {
            $description .= ". Temps de préparation : {$recipe->getCookingTime()} minutes";
        }
        
        if ($recipe->getDescription()) {
            $description .= ". " . strip_tags($recipe->getDescription());
        }
        
        if ($recipe->getIngredients()) {
            $ingredients = implode(', ', array_slice($recipe->getIngredients(), 0, 5));
            $description .= ". Ingrédients principaux : {$ingredients}";
        }
        
        if ($recipe->getReviews()->count() > 0) {
            $rating = $recipe->getRating() ?? 4.5;
            $reviewCount = $recipe->getReviews()->count();
            $description .= ". Note moyenne : {$rating}/5 ({$reviewCount} avis)";
        }
        
        $description .= ". Recette française facile et rapide sur KocinaSpeed.";
        
        return $description;
    }

    /**
     * Génère des mots-clés optimisés pour le SEO
     */
    public function generateSeoKeywords(Recipe $recipe): array
    {
        $keywords = [
            $recipe->getName(),
            "recette " . strtolower($recipe->getName()),
            "cuisine française",
            "recette rapide",
            "recette facile"
        ];

        if ($recipe->getCategory()) {
            $keywords[] = $recipe->getCategory();
            $keywords[] = "recette " . strtolower($recipe->getCategory());
        }

        if ($recipe->getCookingTime()) {
            $keywords[] = "{$recipe->getCookingTime()} minutes";
            $keywords[] = "recette {$recipe->getCookingTime()} min";
        }

        // Ajouter des mots-clés basés sur les ingrédients
        if ($recipe->getIngredients()) {
            foreach (array_slice($recipe->getIngredients(), 0, 3) as $ingredient) {
                $ingredient = trim(strtolower($ingredient));
                if (strlen($ingredient) > 2) {
                    $keywords[] = "recette avec " . $ingredient;
                }
            }
        }

        $keywords[] = "KocinaSpeed";
        $keywords[] = "cuisine maison";
        $keywords[] = "recette photo";

        return array_unique($keywords);
    }

    /**
     * Génère des balises hreflang pour le SEO international
     */
    public function generateHreflangTags(Request $request): array
    {
        $baseUrl = $request->getSchemeAndHttpHost();
        $path = $request->getPathInfo();
        
        return [
            'fr' => $baseUrl . $path,
            'fr-FR' => $baseUrl . $path,
            'x-default' => $baseUrl . $path
        ];
    }

    /**
     * Optimise le title tag pour les moteurs de recherche et IA
     */
    public function optimizePageTitle(string $baseTitle, ?Recipe $recipe = null): string
    {
        if ($recipe) {
            $title = "{$recipe->getName()} - Recette Facile et Rapide";
            
            if ($recipe->getCookingTime()) {
                $title .= " ({$recipe->getCookingTime()} min)";
            }
            
            $title .= " | KocinaSpeed";
            
            // Limiter à 60 caractères pour Google
            if (strlen($title) > 60) {
                $title = substr($title, 0, 57) . "...";
            }
            
            return $title;
        }
        
        return $baseTitle;
    }

    /**
     * Génère des données structurées pour les rich snippets
     */
    public function generateRecipeStructuredData(Recipe $recipe, Request $request): array
    {
        $baseUrl = $request->getSchemeAndHttpHost();
        
        $structuredData = [
            "@context" => "https://schema.org/",
            "@type" => "Recipe",
            "name" => $recipe->getName(),
            "description" => $this->generateAiOptimizedDescription($recipe),
            "datePublished" => $recipe->getCreatedAt()->format('Y-m-d'),
            "author" => [
                "@type" => "Organization",
                "name" => "KocinaSpeed"
            ],
            "publisher" => [
                "@type" => "Organization", 
                "name" => "KocinaSpeed",
                "logo" => [
                    "@type" => "ImageObject",
                    "url" => $baseUrl . "/img/logos/logo.png"
                ]
            ]
        ];

        // Images
        if ($recipe->getImages()->count() > 0) {
            $images = [];
            foreach ($recipe->getImages() as $image) {
                $images[] = $baseUrl . "/uploads/recipes/" . $image->getImagePath();
            }
            $structuredData["image"] = $images;
        }

        // Temps de préparation
        if ($recipe->getCookingTime()) {
            $structuredData["prepTime"] = "PT{$recipe->getCookingTime()}M";
            $structuredData["cookTime"] = "PT{$recipe->getCookingTime()}M";
            $structuredData["totalTime"] = "PT" . ($recipe->getCookingTime() * 2) . "M";
        }

        // Ingrédients
        if ($recipe->getIngredients()) {
            $structuredData["recipeIngredient"] = $recipe->getIngredients();
        }

        // Instructions
        $structuredData["recipeInstructions"] = [
            [
                "@type" => "HowToStep",
                "text" => $recipe->getDescription()
            ]
        ];

        // Catégorie et cuisine
        $structuredData["recipeCategory"] = $recipe->getCategory() ?? "Cuisine générale";
        $structuredData["recipeCuisine"] = "Française";

        // Avis et notes
        if ($recipe->getReviews()->count() > 0) {
            $structuredData["aggregateRating"] = [
                "@type" => "AggregateRating",
                "ratingValue" => $recipe->getRating() ?? 4.5,
                "reviewCount" => $recipe->getReviews()->count(),
                "bestRating" => 5,
                "worstRating" => 1
            ];
        }

        // Vidéo
        if ($recipe->getVideo()) {
            $structuredData["video"] = [
                "@type" => "VideoObject",
                "name" => $recipe->getName() . " - Vidéo recette",
                "description" => "Apprenez à préparer " . $recipe->getName(),
                "contentUrl" => $recipe->getVideo(),
                "embedUrl" => $recipe->getVideo(),
                "uploadDate" => $recipe->getCreatedAt()->format('Y-m-d')
            ];
        }

        return $structuredData;
    }
}