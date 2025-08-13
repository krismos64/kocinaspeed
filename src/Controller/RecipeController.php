<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\RecipeImage;
use App\Entity\Review;
use App\Entity\ReviewImage;
use App\Form\RecipeType;
use App\Form\ReviewType;
use App\Repository\RecipeRepository;
use App\Service\CacheService;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class RecipeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(RecipeRepository $recipeRepository, CacheService $cacheService): Response
    {
        // Utilisation du cache pour optimiser les performances
        $homeData = $cacheService->getHomeData(function() use ($recipeRepository) {
            return [
                'latestRecipes' => $recipeRepository->findLatestWithImages(6),
                'allRecipes' => $recipeRepository->findAllWithImages()
            ];
        });

        return $this->render('pages/home.html.twig', [
            'recipes' => $homeData['latestRecipes'],
            'allRecipes' => $homeData['allRecipes'],
        ]);
    }

    #[Route('/recettes/{category}', name: 'app_recipe_index', defaults: ['category' => 'all'])]
    public function recipeList(RecipeRepository $recipeRepository, Request $request, string $category = 'all'): Response
    {
        $page = $request->query->getInt('page', 1);

        // Récupère toutes les recettes avec images pour le menu de navigation
        $allRecipes = $recipeRepository->findAllWithImages();
        
        // Si on filtre par catégorie, on filtre les résultats
        if ($category !== 'all') {
            $allRecipes = array_filter($allRecipes, function($recipe) use ($category) {
                return $recipe->getCategory() === $category;
            });
        }

        // Pagination optimisée avec les relations
        $queryBuilder = $recipeRepository->createOptimizedQueryBuilder($category);
        $adapter = new QueryAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(9);
        $pagerfanta->setCurrentPage($page);

        return $this->render('recipe/index.html.twig', [
            'pager' => $pagerfanta,
            'category' => $category,
            'categories' => Recipe::CATEGORIES,
            'allRecipes' => $allRecipes,
        ]);
    }

    #[Route('/recette/{slug}', name: 'app_recipe_details')]
    public function show(RecipeRepository $recipeRepository, string $slug, CacheService $cacheService): Response
    {
        // Utilisation du cache pour les détails de la recette
        $recipe = $cacheService->getRecipeData($slug, function() use ($recipeRepository, $slug) {
            return $recipeRepository->findOneBySlugWithRelations($slug);
        });

        if (!$recipe) {
            throw $this->createNotFoundException('Aucune recette trouvée pour le slug ' . $slug);
        }

        $videoId = null;
        if ($recipe->getVideo()) {
            $videoId = $this->extractYoutubeId($recipe->getVideo());
        }

        $recipeImages = $recipe->getImages();

        return $this->render('recipe/details.html.twig', [
            'recipe' => $recipe,
            'videoId' => $videoId,
            'recipeImages' => $recipeImages,
        ]);
    }

    #[Route('/recette/ajouter', name: 'app_recipe_new')]
    public function addRecipe(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, CacheService $cacheService): Response
    {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recipe->setSlug($slugger->slug($recipe->getName()));

            $imageForms = $form->get('images');
            foreach ($imageForms as $imageForm) {
                $image = $imageForm->getData();
                $imageFile = $imageForm->get('imageFile')->getData();

                if ($imageFile) {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                    try {
                        $imageFile->move(
                            $this->getParameter('recipe_images_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                        continue;
                    }

                    $image->setImagePath($newFilename);
                    $image->setRecipe($recipe);
                    $recipe->addImage($image);
                }
            }

            $entityManager->persist($recipe);
            $entityManager->flush();
            
            // Invalider le cache après ajout d'une nouvelle recette
            $cacheService->invalidateAllRecipes();

            $this->addFlash('success', 'La recette a bien été ajoutée.');

            return $this->redirectToRoute('app_recipe_details', ['slug' => $recipe->getSlug()]);
        }

        return $this->render('recipe/recipe_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/recette/{slug}/laisser-un-avis', name: 'app_recipe_review')]
    public function reviewForm(
        string $slug,
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        MailerInterface $mailer
    ): Response {
        $recipe = $entityManager->getRepository(Recipe::class)->findOneBy(['slug' => $slug]);

        if (!$recipe) {
            throw $this->createNotFoundException('Recette non trouvée');
        }

        $review = new Review();
        $review->setRecipe($recipe);

        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $review->setUser($this->getUser());
            $review->setApproved(false);

            $imagesData = $form->get('images');
            foreach ($imagesData as $imageForm) {
                $imageFile = $imageForm->get('imageFile')->getData();

                if ($imageFile) {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                    try {
                        $imageFile->move(
                            $this->getParameter('review_images_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                        continue;
                    }

                    $reviewImage = new ReviewImage();
                    $reviewImage->setImagePath($newFilename);
                    $reviewImage->setReview($review);
                    $review->addImage($reviewImage);
                }
            }

            $entityManager->persist($review);
            $entityManager->flush();

            $email = (new Email())
                ->from('support@kocinaspeed.fr')
                ->to('support@kocinaspeed.fr')
                ->subject('Nouvel avis en attente d\'approbation')
                ->html('<p>Un nouvel avis a été soumis pour la recette "' . $recipe->getName() . '". Veuillez vérifier et approuver.</p>');

            $mailer->send($email);

            $this->addFlash('success', 'Merci, nous avons bien reçu votre avis !');

            return $this->redirectToRoute('app_recipe_details', ['slug' => $recipe->getSlug()]);
        }

        return $this->render('recipe/review_form.html.twig', [
            'recipe' => $recipe,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/recherche', name: 'app_recipe_search')]
    public function search(RecipeRepository $recipeRepository, Request $request, CacheService $cacheService): Response
    {
        $query = $request->query->get('query');
        if ($query) {
            // Utilisation du cache pour les résultats de recherche
            $recipes = $cacheService->getSearchResults($query, function() use ($recipeRepository, $query) {
                return $recipeRepository->findBySearchQuery($query);
            });
        } else {
            $recipes = [];
        }

        return $this->render('recipe/search_results.html.twig', [
            'recipes' => $recipes,
            'query' => $query,
        ]);
    }

    private function extractYoutubeId(string $videoUrl): ?string
    {
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $videoUrl, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
