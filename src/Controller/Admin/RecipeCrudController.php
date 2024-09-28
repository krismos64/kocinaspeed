<?php

namespace App\Controller\Admin;

use App\Entity\Recipe;
use App\Entity\RecipeImage;
use App\Form\RecipeImageType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class RecipeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Recipe::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Titre'),
            SlugField::new('slug')->setTargetFieldName('name')->hideOnIndex(),
            ChoiceField::new('category', 'Catégorie')->setChoices(array_flip(Recipe::CATEGORIES)),
            TextEditorField::new('description', 'Description'),
            TextField::new('ingredients', 'Ingrédients'),
            NumberField::new('cookingTime', 'Temps de cuisson (minutes)'),
            CollectionField::new('images', 'Images')
                ->setEntryType(RecipeImageType::class)
                ->setFormTypeOptions(['by_reference' => false])
                ->onlyOnForms(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Recipe) {
            foreach ($entityInstance->getImages() as $image) {
                /** @var RecipeImage $image */
                $imageFile = $image->getImageFile();
                if ($imageFile) {
                    // Génération du nom unique pour l'image
                    $newFilename = md5(uniqid()) . '.' . $imageFile->guessExtension();

                    try {
                        // Déplacer l'image téléchargée dans le répertoire
                        $imageFile->move(
                            $this->getParameter('recipe_images_directory'),
                            $newFilename
                        );

                        // Mettre à jour le chemin de l'image dans l'entité RecipeImage
                        $image->setImagePath($newFilename);
                        $image->setRecipe($entityInstance); // Relier l'image à la recette
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                    }
                }
            }
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Recipe) {
            foreach ($entityInstance->getImages() as $image) {
                /** @var RecipeImage $image */
                $imageFile = $image->getImageFile();
                if ($imageFile) {
                    // Suppression de l'ancienne image si elle existe
                    if ($image->getImagePath()) {
                        $oldImagePath = $this->getParameter('recipe_images_directory') . '/' . $image->getImagePath();
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }

                    // Génération du nouveau nom pour l'image
                    $newFilename = md5(uniqid()) . '.' . $imageFile->guessExtension();

                    try {
                        // Déplacer la nouvelle image
                        $imageFile->move(
                            $this->getParameter('recipe_images_directory'),
                            $newFilename
                        );

                        // Mettre à jour le chemin de l'image dans l'entité
                        $image->setImagePath($newFilename);
                        $image->setRecipe($entityInstance); // Relier l'image à la recette

                    } catch (FileException $e) {
                        $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                    }
                }
            }
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
