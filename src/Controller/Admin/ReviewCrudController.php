<?php

namespace App\Controller\Admin;

use App\Entity\Review;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ReviewCrudController extends AbstractCrudController
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public static function getEntityFqcn(): string
    {
        return Review::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('visitorName', 'Nom de l\'utilisateur'),
            TextField::new('visitorEmail', 'Email de l\'utilisateur')->hideOnIndex(),
            AssociationField::new('recipe', 'Recette')->setRequired(true),
            IntegerField::new('rating', 'Note')
                ->setFormTypeOptions(['attr' => ['min' => 1, 'max' => 5]]),
            TextareaField::new('comment', 'Commentaire'),
            DateTimeField::new('createdAt', 'Créé le')
                ->setFormTypeOptions(['widget' => 'single_text'])
                ->setRequired(true),
            BooleanField::new('approved', 'Approuvé ?'),
            ImageField::new('images', 'Images')
                ->setBasePath('uploads/reviews/')
                ->setUploadDir('public/uploads/reviews/')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false)
                ->setFormTypeOptions([
                    'multiple' => true,
                    'data_class' => null,
                    'mapped' => false,
                ])
                ->setHelp('Vous pouvez télécharger plusieurs images (formats acceptés : JPG, PNG)'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $request = $this->requestStack->getCurrentRequest();
        /** @var UploadedFile[] $imageFiles */
        $imageFiles = $request->files->get('Review')['images'] ?? [];
        $images = [];

        if ($imageFiles) {
            foreach ($imageFiles as $imageFile) {
                if ($imageFile instanceof UploadedFile) {
                    $newFilename = md5(uniqid()) . '.' . $imageFile->guessExtension();

                    // Déplacer le fichier dans le répertoire uploads
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
            $entityInstance->setImages($images);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        // Même logique que dans persistEntity pour gérer les images lors de la mise à jour
        $request = $this->requestStack->getCurrentRequest();
        /** @var UploadedFile[] $imageFiles */
        $imageFiles = $request->files->get('Review')['images'] ?? [];
        $images = $entityInstance->getImages();

        if ($imageFiles) {
            foreach ($imageFiles as $imageFile) {
                if ($imageFile instanceof UploadedFile) {
                    $newFilename = md5(uniqid()) . '.' . $imageFile->guessExtension();

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

            $entityInstance->setImages($images);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
