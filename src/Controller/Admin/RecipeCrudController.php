<?php

namespace App\Controller\Admin;

use App\Entity\Recipe;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class RecipeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Recipe::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            ImageField::new('image', 'Image')
                ->setBasePath('uploads/')
                ->setUploadDir('public/uploads')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false),

            TextField::new('name', 'Titre')->setFormTypeOptions([
                'attr' => ['maxlength' => 255],
            ]),

            TextField::new('subtitle', 'Sous-Titre')->setFormTypeOptions([
                'attr' => ['maxlength' => 255],
            ]),

            SlugField::new('slug')->setTargetFieldName('name'),

            TextField::new('video', 'Vidéo')->setFormTypeOptions([
                'attr' => ['maxlength' => 255],
            ]),

            ChoiceField::new('category', 'Catégorie')
                ->setChoices(Recipe::CATEGORIES)
                ->setRequired(true),

            IntegerField::new('rating', 'Note sur 5')
                ->setFormTypeOptions([
                    'attr' => [
                        'min' => 0,
                        'max' => 5,
                    ],
                    'required' => false,
                ]),
        ];

        // Ajouter la description en texte brut pour toutes les vues sauf la vue de formulaire (édition)
        if ($pageName === 'index' || $pageName === 'detail') {
            $fields[] = TextareaField::new('description', 'Description')
                ->setTextAlign('left')
                ->formatValue(function ($value) {
                    return strip_tags($value);
                });

            $fields[] = TextareaField::new('reviews', 'Avis')
                ->setTextAlign('left')
                ->formatValue(function ($value) {
                    return strip_tags($value);
                });
        }

        // Utilisation de TextEditorField pour l'édition dans les formulaires
        if ($pageName === 'new' || $pageName === 'edit') {
            $fields[] = TextEditorField::new('description', 'Description');
            $fields[] = TextEditorField::new('reviews', 'Avis')->setRequired(false);
        }

        return $fields;
    }
}
