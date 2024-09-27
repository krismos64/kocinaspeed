<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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

    // Propriété non mappée pour stocker le fichier temporairement lors de l'upload
    private ?UploadedFile $imageFile = null;

    // Getter et setter pour l'imageFile
    public function getImageFile(): ?UploadedFile
    {
        return $this->imageFile;
    }

    public function setImageFile(?UploadedFile $imageFile): self
    {
        $this->imageFile = $imageFile;
        return $this;
    }

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

    // Fonction pour gérer l'upload et définir le chemin de l'image
    public function uploadImage(string $directory): void
    {
        if ($this->imageFile) {
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
