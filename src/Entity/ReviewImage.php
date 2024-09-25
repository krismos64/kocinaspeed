<?php

namespace App\Entity;

use App\Repository\ReviewImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReviewImageRepository::class)]
class ReviewImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Review::class, inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Review $review = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $imagePath = null;

    /**
     * Cette propriété ne sera pas stockée dans la base de données, elle est utilisée
     * temporairement pour le téléchargement du fichier.
     */
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

    public function getReview(): ?Review
    {
        return $this->review;
    }

    public function setReview(?Review $review): self
    {
        $this->review = $review;

        return $this;
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

        // Mettre à jour `imagePath` uniquement si une nouvelle image a été téléchargée
        if ($imageFile instanceof UploadedFile) {
            // Cela permet de mettre à jour l'imagePath avec un nom de fichier temporaire
            // Si vous avez besoin de mettre à jour la propriété imagePath ici, 
            // par exemple : $this->imagePath = $newFilename;
        }
    }
}
