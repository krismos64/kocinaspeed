<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\RecipeImage;
use App\Entity\Review;
use App\Entity\ReviewImage;
use App\Form\RecipeType;
use App\Form\ReviewType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    #[Route('/recette/{slug}', name: 'app_recipe_details')]
    public function show(
        RecipeRepository $recipeRepository,
        string $slug
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

        // Récupération des images associées à la recette
        $recipeImages = $recipe->getImages();

        return $this->render('recipe/details.html.twig', [
            'recipe' => $recipe,
            'videoId' => $videoId,
            'recipeImages' => $recipeImages,
        ]);
    }

    #[Route('/recette/ajouter', name: 'app_recipe_new')]
    public function addRecipe(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Génération du slug pour la recette
            $recipe->setSlug($slugger->slug($recipe->getName()));

            // Gestion des images uploadées pour la recette
            $imageForms = $form->get('images');
            foreach ($imageForms as $imageForm) {
                /** @var RecipeImage $image */
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

                    // Mettre à jour le chemin de l'image dans l'entité
                    $image->setImagePath($newFilename);
                    $image->setRecipe($recipe); // Lier l'image à la recette
                    $recipe->addImage($image); // Associer l'image à la recette
                }
            }

            $entityManager->persist($recipe);
            $entityManager->flush();

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
            foreach ($imagesData as $key => $imageForm) {
                /** @var UploadedFile $imageFile */
                $imageFile = $imageForm->get('imageFile')->getData();

                if ($imageFile) {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' .
                        $imageFile->guessExtension();

                    // Déplacer le fichier dans le répertoire de destination
                    try {
                        $imageFile->move(
                            $this->getParameter('review_images_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        $this->addFlash(
                            'error',
                            'Une erreur est survenue lors du téléchargement de l\'image.'
                        );
                        continue; // Passer à l'image suivante en cas d'erreur
                    }

                    // Créer une nouvelle entité ReviewImage seulement si une image a été téléchargée
                    $reviewImage = new ReviewImage();
                    $reviewImage->setImagePath($newFilename);
                    $reviewImage->setReview($review);

                    // Ajouter l'image à la collection de l'avis
                    $review->addImage($reviewImage); // Correction du 'reviewImagee'
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

    #[Route('/recettes', name: 'app_recipe_index')]
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

    #[Route('/recherche', name: 'app_recipe_search')]
    public function search(RecipeRepository $recipeRepository, Request $request): Response
    {
        $query = $request->query->get('query');
        if ($query) {
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

    // Méthode privée pour extraire l'ID de la vidéo YouTube
    private function extractYoutubeId(string $videoUrl): ?string
    {
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $videoUrl, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
