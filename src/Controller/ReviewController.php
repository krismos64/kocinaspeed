<?php

namespace App\Controller;

use App\Entity\Review;
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
        $review->setCreatedAt(new \DateTimeImmutable());

        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Traitement des images téléchargées
            $imageFiles = $form->get('images')->getData();
            $images = [];

            if ($imageFiles) {
                foreach ($imageFiles as $imageFile) {
                    // Générer un nom unique pour l'image
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    // Sécuriser le nom de fichier
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                    // Déplacer le fichier dans le dossier de destination
                    try {
                        $imageFile->move(
                            $this->getParameter('reviews_images_directory'),
                            $newFilename
                        );
                        $images[] = 'uploads/reviews/' . $newFilename;
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image.');
                    }
                }
            }

            // Stocker les chemins des images dans l'entité
            $review->setImages($images);

            // Par défaut, l'avis n'est pas approuvé
            $review->setApproved(false);

            // Enregistrement de l'avis
            $entityManager->persist($review);
            $entityManager->flush();

            // Redirection vers la page de détail de la recette
            $this->addFlash('success', 'Votre avis a été soumis et sera approuvé par un administrateur.');

            return $this->redirectToRoute('app_recipe_details', ['slug' => $slug]);
        }

        return $this->render('review/add_review.html.twig', [
            'form' => $form->createView(),
            'recipe' => $recipe,
        ]);
    }
}
