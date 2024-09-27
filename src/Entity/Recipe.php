<?php

namespace App\Entity;

use App\Repository\RecipeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecipeRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Recipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null; // Description ou étapes de préparation

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $ingredients = null; // Ingrédients de la recette

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $cookingTime = null; // Temps de cuisson en minutes

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $video = null; // URL vidéo optionnelle

    #[ORM\OneToMany(mappedBy: 'recipe', targetEntity: RecipeImage::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $images; // Images pour le slider

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $rating = null; // Note moyenne des avis

    #[ORM\OneToMany(mappedBy: 'recipe', targetEntity: Review::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $reviews; // Collection d'avis

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\Column(length: 255)]
    private ?string $category = null; // Catégorie de la recette

    const CATEGORIES = [
        'DESSERTS' => 'Desserts',
        'PLATS' => 'Plats',
        'APERITIFS' => 'Apéritifs',
    ];

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
        $this->images = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->created_at = new \DateTimeImmutable();
        $this->updated_at = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updated_at = new \DateTimeImmutable();
        $this->calculateAverageRating(); // Calcul de la note moyenne lors des mises à jour
    }

    // Getters et setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getIngredients(): ?string
    {
        return $this->ingredients;
    }

    public function setIngredients(?string $ingredients): self
    {
        $this->ingredients = $ingredients;
        return $this;
    }

    public function getCookingTime(): ?int
    {
        return $this->cookingTime;
    }

    public function setCookingTime(?int $cookingTime): self
    {
        $this->cookingTime = $cookingTime;
        return $this;
    }

    public function getVideo(): ?string
    {
        return $this->video;
    }

    public function setVideo(?string $video): self
    {
        $this->video = $video;
        return $this;
    }

    public function getRating(): ?float
    {
        return $this->rating;
    }

    public function setRating(?float $rating): self
    {
        $this->rating = $rating;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;
        return $this;
    }

    // Gestion des avis (reviews)

    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): self
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setRecipe($this);
        }

        $this->calculateAverageRating(); // Recalculer la note moyenne après ajout
        return $this;
    }

    public function removeReview(Review $review): self
    {
        if ($this->reviews->removeElement($review)) {
            if ($review->getRecipe() === $this) {
                $review->setRecipe(null);
            }
        }

        $this->calculateAverageRating(); // Recalculer la note moyenne après suppression
        return $this;
    }

    // Calcul de la note moyenne des avis approuvés
    public function calculateAverageRating(): void
    {
        $approvedReviews = $this->reviews->filter(function ($review) {
            return $review->isApproved();
        });

        if ($approvedReviews->count() > 0) {
            $totalRating = array_sum($approvedReviews->map(function ($review) {
                return $review->getRating();
            })->toArray());

            // Mise à jour de la note moyenne, arrondie à 2 décimales
            $this->rating = round($totalRating / $approvedReviews->count(), 2);
        } else {
            $this->rating = null; // Si aucun avis approuvé
        }
    }

    // Gestion des images (RecipeImage)

    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(RecipeImage $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setRecipe($this);
        }
        return $this;
    }

    public function removeImage(RecipeImage $image): self
    {
        if ($this->images->removeElement($image)) {
            if ($image->getRecipe() === $this) {
                $image->setRecipe(null);
            }
        }
        return $this;
    }
}
