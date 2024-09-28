<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Recipe; // Utilise l'entité réelle Recipe, pas le proxy
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class RecipeImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $imagePath = null;

    #[ORM\ManyToOne(targetEntity: Recipe::class, inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Recipe $recipe = null;

    #[Assert\File(
        maxSize: '5M',
        mimeTypes: ['image/jpeg', 'image/png'],
        mimeTypesMessage: 'Veuillez télécharger une image valide (JPG ou PNG).'
    )]
    private ?File $imageFile = null;

    // Getters et Setters pour les propriétés

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): self
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    public function getRecipe(): ?Recipe
    {
        return $this->recipe;
    }

    public function setRecipe(?Recipe $recipe): self
    {
        $this->recipe = $recipe;

        return $this;
    }

    /**
     * @return File|UploadedFile|null
     */
    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    /**
     * @param File|UploadedFile|null $imageFile
     */
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;
    }

    // Méthode pour gérer l'upload de l'image et définir le chemin
    public function uploadImage(string $directory): void
    {
        if ($this->imageFile instanceof UploadedFile) {
            $newFilename = uniqid() . '.' . $this->imageFile->guessExtension();

            try {
                // Déplacer l'image vers le bon répertoire
                $this->imageFile->move($directory, $newFilename);

                // Mettre à jour le chemin de l'image dans l'entité
                $this->setImagePath($newFilename);
            } catch (\Exception $e) {
                throw new \Exception('Erreur lors du téléchargement de l\'image : ' . $e->getMessage());
            }
        }
    }
}
