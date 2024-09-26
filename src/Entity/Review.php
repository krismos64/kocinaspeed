<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La note est obligatoire.")]
    #[Assert\Range(
        min: 1,
        max: 5,
        notInRangeMessage: "La note doit être comprise entre {{ min }} et {{ max }}.",
    )]
    private ?int $rating = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Le commentaire ne peut pas être vide.")]
    private ?string $comment = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?bool $approved = false;

    #[ORM\ManyToOne(targetEntity: Recipe::class, inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Recipe $recipe = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le nom du visiteur ne peut pas dépasser {{ limit }} caractères.",
    )]
    private ?string $visitorName = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Email(message: "L'adresse email n'est pas valide.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "L'email du visiteur ne peut pas dépasser {{ limit }} caractères.",
    )]
    private ?string $visitorEmail = null;

    #[ORM\OneToMany(mappedBy: 'review', targetEntity: ReviewImage::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $images;

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    /**
     * @return Collection<int, ReviewImage>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(ReviewImage $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setReview($this);
        }

        return $this;
    }

    public function removeImage(ReviewImage $image): self
    {
        if ($this->images->removeElement($image)) {
            if ($image->getReview() === $this) {
                $image->setReview(null);
            }
        }

        return $this;
    }

    // Gestion des champs de l'avis
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): self
    {
        $this->rating = $rating;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    public function isApproved(): ?bool
    {
        return $this->approved;
    }

    public function setApproved(bool $approved): self
    {
        $this->approved = $approved;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getVisitorName(): ?string
    {
        return $this->visitorName;
    }

    public function setVisitorName(?string $visitorName): self
    {
        $this->visitorName = $visitorName;

        return $this;
    }

    public function getVisitorEmail(): ?string
    {
        return $this->visitorEmail;
    }

    public function setVisitorEmail(?string $visitorEmail): self
    {
        $this->visitorEmail = $visitorEmail;

        return $this;
    }

    // Appeler le recalcul de la note moyenne avant de persister, mettre à jour ou supprimer un avis
    #[ORM\PreUpdate]
    #[ORM\PrePersist]
    public function updateRecipeRating(): void
    {
        if ($this->recipe) {
            $this->recipe->calculateAverageRating(); // Met à jour la note moyenne de la recette
        }
    }

    #[ORM\PostRemove]
    public function updateRecipeRatingOnRemove(): void
    {
        if ($this->recipe) {
            $this->recipe->calculateAverageRating(); // Met à jour la note moyenne si l'avis est supprimé
        }
    }
}
