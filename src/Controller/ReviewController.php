<?php

namespace App\Controller;

use App\Entity\Review;
use App\Entity\ReviewImage;
use App\Form\ReviewType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ReviewController extends AbstractController
{
    #[Route('/recipe/{slug}/review', name: 'recipe_review')]
    public function addReview(
        Request $request,
        RecipeRepository $recipeRepository,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        string $slug
    ): Response {
        $recipe = $recipeRepository->findOneBy(['slug' => $slug]);

        if (!$recipe) {
            throw $this->createNotFoundException('Recette non trouvée');
        }

        $review = new Review();
        $review->setRecipe($recipe);

        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Par défaut, l'avis n'est pas approuvé
            $review->setApproved(false);

            // Gestion des images associées à l'avis
            $imagesData = $form->get('images');

            foreach ($imagesData as $key => $imageForm) {
                $imageFile = $imageForm->get('imageFile')->getData();

                // Créer une nouvelle entité ReviewImage UNIQUEMENT si une image est uploadée
                if ($imageFile) {
                    $originalFilename = pathinfo(
                        $imageFile->getClientOriginalName(),
                        PATHINFO_FILENAME
                    );
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                    // Déplacer le fichier dans le répertoire de destination
                    try {
                        $imageFile->move(
                            $this->getParameter('reviews_images_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        $this->addFlash(
                            'error',
                            'Une erreur est survenue lors du téléchargement de l\'image.'
                        );
                        continue; // Passer à l'image suivante en cas d'erreur
                    }

                    // Créer et associer une nouvelle entité ReviewImage
                    $reviewImage = new ReviewImage();
                    $reviewImage->setImagePath($newFilename);
                    $reviewImage->setReview($review);

                    // Ajouter l'image à la collection de l'avis
                    $review->addImage($reviewImage);
                }
            }

            // Enregistrement de l'avis et des images associées
            $entityManager->persist($review);
            $entityManager->flush();

            // Redirection après succès
            $this->addFlash('success', 'Votre avis a été soumis et sera approuvé par un administrateur.');
            return $this->redirectToRoute('app_recipe_details', ['slug' => $slug]);
        }

        return $this->render('review/add_review.html.twig', [
            'form' => $form->createView(),
            'recipe' => $recipe,
        ]);
    }
}
