<?php

namespace App\Controller\Admin;

use App\Entity\Recipe;
use App\Entity\RecipeImage;
use App\Form\RecipeImageType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\FileType;
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
                    // Appel à la méthode pour uploader l'image
                    $image->uploadImage($this->getParameter('recipe_images_directory'));

                    // Vérifier que l'imagePath a bien été définie
                    if ($image->getImagePath() === null) {
                        throw new \Exception('Le chemin de l\'image n\'a pas été défini après l\'upload.');
                    }

                    // Associer l'image à la recette
                    $image->setRecipe($entityInstance);
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

                    // Appelle la méthode uploadImage dans l'entité RecipeImage pour gérer l'upload
                    $image->uploadImage($this->getParameter('recipe_images_directory'));
                    $image->setRecipe($entityInstance); // Relie l'image à la recette
                }
            }
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
