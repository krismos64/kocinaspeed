<?php

namespace App\Controller\Admin;

use App\Entity\Recipe;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField; // Utilisation de DateField
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField; // Utilisation de NumberField
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class RecipeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Recipe::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            TextField::new('name', 'Titre')->setFormTypeOptions([
                'attr' => ['maxlength' => 255],
            ]),

            SlugField::new('slug')
                ->setTargetFieldName('name')
                ->hideOnIndex(),

            TextField::new('subtitle', 'Sous-Titre')->setFormTypeOptions([
                'attr' => ['maxlength' => 255],
            ]),

            ChoiceField::new('category', 'Catégorie')
                ->setChoices(array_flip(Recipe::CATEGORIES))
                ->setRequired(true),

            ImageField::new('image', 'Image')
                ->setBasePath('uploads/')
                ->setUploadDir('public/uploads')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false),

            TextField::new('video', 'Vidéo')->setFormTypeOptions([
                'attr' => ['maxlength' => 255],
            ])->hideOnIndex(),

            // Note moyenne calculée avec NumberField
            NumberField::new('rating', 'Note moyenne')
                ->setFormTypeOptions([
                    'attr' => [
                        'min' => 0,
                        'max' => 5,
                        'step' => 0.1,
                    ],
                ])
                ->onlyOnDetail(),

            // Champs de date avec DateField
            DateField::new('created_at', 'Créée le')
                ->setFormat('short')
                ->onlyOnDetail(),

            DateField::new('updated_at', 'Mise à jour le')
                ->setFormat('short')
                ->onlyOnDetail(),
        ];

        // Champs spécifiques aux pages de création et d'édition
        if ($pageName === Crud::PAGE_NEW || $pageName === Crud::PAGE_EDIT) {
            $fields[] = TextEditorField::new('description', 'Description');
            // Les avis ne doivent pas être éditables directement depuis la recette
        } else {
            $fields[] = TextareaField::new('description', 'Description')
                ->formatValue(function ($value) {
                    return strip_tags($value);
                })
                ->onlyOnDetail();
        }

        // Afficher les avis associés dans la page de détail
        $fields[] = AssociationField::new('reviews', 'Avis')
            ->hideOnForm();

        return $fields;
    }
}
