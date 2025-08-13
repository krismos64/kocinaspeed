<?php

namespace App\Entity;

use App\Repository\RecipeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecipeRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Index(name: 'idx_recipe_slug', columns: ['slug'])]
#[ORM\Index(name: 'idx_recipe_category', columns: ['category'])]
#[ORM\Index(name: 'idx_recipe_created_at', columns: ['created_at'])]
#[ORM\Index(name: 'idx_recipe_name', columns: ['name'])]
#[ORM\Index(name: 'idx_recipe_search', columns: ['name', 'category'])]
class Recipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $ingredients = [];

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $cookingTime = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $video = null;

    #[ORM\OneToMany(mappedBy: 'recipe', targetEntity: RecipeImage::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $images;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $rating = null;

    #[ORM\OneToMany(mappedBy: 'recipe', targetEntity: Review::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $reviews;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255, options: ['default' => 'PLATS'])]
    private ?string $category = null;

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
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
        $this->calculateAverageRating();
    }

    public function __toString(): string
    {
        return $this->name ?: 'Recette';
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

    public function getIngredients(): ?array
    {
        return $this->ingredients;
    }

    public function setIngredients(?array $ingredients): self
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
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
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

        $this->calculateAverageRating();
        return $this;
    }

    public function removeReview(Review $review): self
    {
        if ($this->reviews->removeElement($review)) {
            if ($review->getRecipe() === $this) {
                $review->setRecipe(null);
            }
        }

        $this->calculateAverageRating();
        return $this;
    }

    public function calculateAverageRating(): void
    {
        $approvedReviews = $this->reviews->filter(function ($review) {
            return $review->isApproved();
        });

        if ($approvedReviews->count() > 0) {
            $totalRating = array_sum($approvedReviews->map(function ($review) {
                return $review->getRating();
            })->toArray());

            $this->rating = round($totalRating / $approvedReviews->count(), 2);
        } else {
            $this->rating = null;
        }
    }
}
