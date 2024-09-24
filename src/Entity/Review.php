<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $rating = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $comment = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $images = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\PrePersist] // Cette méthode sera appelée automatiquement avant l'insertion
    public function setCreatedAtValue(): void
    {
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    #[ORM\Column]
    private ?bool $approved = false;

    #[ORM\ManyToOne(targetEntity: Recipe::class, inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Recipe $recipe = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $visitorName = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $visitorEmail = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): static
    {
        $this->rating = $rating;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getImages(): ?array
    {
        // Convertir la chaîne d'images en tableau
        return $this->images ? explode(',', $this->images) : [];
    }

    public function setImages(?array $images): static
    {
        // Convertir le tableau d'images en chaîne séparée par des virgules
        $this->images = $images ? implode(',', $images) : null;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function isApproved(): ?bool
    {
        return $this->approved;
    }

    public function setApproved(bool $approved): static
    {
        $this->approved = $approved;

        return $this;
    }

    public function getRecipe(): ?Recipe
    {
        return $this->recipe;
    }

    public function setRecipe(?Recipe $recipe): static
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
}
