<?php

namespace App\Controller\Admin;

use App\Entity\Review;
use App\Entity\ReviewImage;
use App\Form\ReviewImageType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use Symfony\Component\HttpFoundation\RequestStack;
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
        // Définir le champ createdAt pour qu'il soit visible uniquement sur les pages d'index et de détails
        $createdAt = DateTimeField::new('createdAt', 'Créé le')
            ->setFormTypeOptions(['widget' => 'single_text'])
            ->hideOnForm();

        return [
            TextField::new('visitorName', 'Nom de l\'utilisateur'),
            TextField::new('visitorEmail', 'Email de l\'utilisateur')->hideOnIndex(),
            AssociationField::new('recipe', 'Recette')->setRequired(true),
            IntegerField::new('rating', 'Note')
                ->setFormTypeOptions(['attr' => ['min' => 1, 'max' => 5]]),
            TextareaField::new('comment', 'Commentaire'),
            $createdAt,
            BooleanField::new('approved', 'Approuvé ?'),
            CollectionField::new('images', 'Images')
                ->setEntryType(ReviewImageType::class)
                ->allowAdd(true)
                ->allowDelete(true)
                ->setFormTypeOptions([
                    'by_reference' => false,
                    'entry_options' => ['label' => false],
                ])
                ->setHelp('Vous pouvez ajouter plusieurs images (formats acceptés : JPG, PNG)'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Review) {
            return;
        }

        foreach ($entityInstance->getImages() as $image) {
            $imageFile = $image->getImageFile(); // Accéder au fichier image à partir de l'entité

            if ($imageFile) {
                $newFilename = md5(uniqid()) . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('reviews_images_directory'),
                        $newFilename
                    );
                    $image->setImagePath('uploads/reviews/' . $newFilename);
                    $this->addFlash('success', 'Image téléchargée avec succès.');
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image.');
                }
            }
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Review) {
            return;
        }

        foreach ($entityInstance->getImages() as $image) {
            $imageFile = $image->getImageFile(); // Accéder au fichier image à partir de l'entité

            if ($imageFile) {
                $newFilename = md5(uniqid()) . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('reviews_images_directory'),
                        $newFilename
                    );
                    $image->setImagePath('uploads/reviews/' . $newFilename);
                    $this->addFlash('success', 'Image téléchargée avec succès.');
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image.');
                }
            }
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
