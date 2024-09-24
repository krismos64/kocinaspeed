<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class RecipeController extends AbstractController
{
    #[Route('/recipe/{slug}', name: 'app_recipe_details')]
    public function show(
        RecipeRepository $recipeRepository,
        string $slug,
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        MailerInterface $mailer
    ): Response {
        $recipe = $recipeRepository->findOneBySlug($slug);

        if (!$recipe) {
            throw $this->createNotFoundException('Aucune recette trouvée pour le slug ' . $slug);
        }

        // Extraction de l'ID de la vidéo YouTube s'il y a une vidéo
        $videoId = null;
        if ($recipe->getVideo()) {
            $videoId = $this->extractYoutubeId($recipe->getVideo());
        }

        // Gestion du formulaire d'avis
        $review = new Review();
        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $review->setRecipe($recipe);
            $review->setUser($this->getUser());
            $review->setCreatedAt(new \DateTimeImmutable());
            $review->setApproved(false); // L'administrateur devra approuver l'avis

            // Gestion des images uploadées
            $images = $form->get('images')->getData();
            $uploadedImages = [];

            if ($images) {
                foreach ($images as $image) {
                    $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();

                    // Déplace le fichier dans le répertoire où sont stockées les images
                    $image->move(
                        $this->getParameter('review_images_directory'),
                        $newFilename
                    );

                    $uploadedImages[] = $newFilename;
                }

                // Enregistre les noms de fichiers dans l'entité Review
                $review->setImages($uploadedImages);
            }

            $entityManager->persist($review);
            $entityManager->flush();

            // Envoi d'une notification par email à l'administrateur
            $email = (new Email())
                ->from('support@kocinaspeed.fr')
                ->to('support@kocinaspeed.fr')
                ->subject('Nouvel avis en attente d\'approbation')
                ->html('<p>Un nouvel avis a été soumis pour la recette "' . $recipe->getName() . '". Veuillez vous connecter à l\'administration pour l\'approuver.</p>');

            $mailer->send($email);

            // Message flash de confirmation
            $this->addFlash('success', 'Merci, nous avons bien reçu votre avis, il est très constructif pour nous !');

            return $this->redirectToRoute('app_recipe_details', ['slug' => $recipe->getSlug()]);
        }

        return $this->render('recipe/details.html.twig', [
            'recipe' => $recipe,
            'videoId' => $videoId,
            'form' => $form->createView(),
        ]);
    }

    // Méthode privée pour extraire l'ID de la vidéo YouTube
    private function extractYoutubeId(string $videoUrl): ?string
    {
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $videoUrl, $matches)) {
            return $matches[1];
        }
        return null;
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
            'query' => $query,
        ]);
    }
}
