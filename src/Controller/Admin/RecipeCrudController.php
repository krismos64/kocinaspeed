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

class RecipeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Recipe::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
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
            TextEditorField::new('description', 'Description'),
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
            TextEditorField::new('reviews', 'Avis')
                ->setRequired(false),
        ];
    }
}
