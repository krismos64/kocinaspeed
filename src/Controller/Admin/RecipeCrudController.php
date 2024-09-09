<?php

namespace App\Controller\Admin;

use App\Entity\Recipe;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
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
            ];

            $slug = SlugField::new('slug')->setTargetFieldName('name');

            $name = TextField::new('name', 'Titre')
            ->setFormTypeOptions([
                'attr' => [
                    'maxlength' => 255
                ]
            ]);

            $subtitle = TextField::new('subtitle', 'Sous-Titre')
            ->setFormTypeOptions([
                'attr' => [
                    'maxlength' => 255
                ]
            ]);

            $video = TextField::new('video', 'Vidéo')
            ->setFormTypeOptions([
                'attr' => [
                    'maxlength' => 255
                ]
            ]);

            $description = TextEditorField::new('description', 'Description');

            $fields[] = $name;
            $fields[] = $subtitle;
            $fields[] = $slug;
            $fields[] = $video;
            $fields[] = $description;



        return $fields;
    }
    
}
