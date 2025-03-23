<?php

namespace App\Entity;

use App\Repository\ItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ItemRepository::class)]
class Item
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "The title cannot be blank.")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "The title must be at least {{ limit }} characters long.",
        maxMessage: "The title cannot be longer than {{ limit }} characters."
    )]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "The description cannot be blank.")]
    #[Assert\Length(
        min: 10,
        max: 255,
        minMessage: "The description must be at least {{ limit }} characters long.",
        maxMessage: "The description cannot be longer than {{ limit }} characters."
    )]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "The price cannot be blank.")]
    #[Assert\Positive(message: "The price must be a positive number.")]
    #[Assert\Type(
        type: 'float',
        message: "The price must be a valid decimal number."
    )]
    private ?float $price = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "The URL cannot be blank.")]
    #[Assert\Url(message: "The URL is not valid.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "The URL cannot be longer than {{ limit }} characters."
    )]
    private ?string $url = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "The wishlist must not be null.")]
    private ?Wishlist $wishlist = null;

    #[ORM\OneToOne(mappedBy: 'item', cascade: ['persist', 'remove'])]
    private ?Purchase $purchase = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getWishlist(): ?Wishlist
    {
        return $this->wishlist;
    }

    public function setWishlist(?Wishlist $wishlist): static
    {
        $this->wishlist = $wishlist;

        return $this;
    }

    public function getPurchase(): ?Purchase
    {
        return $this->purchase;
    }

    public function setPurchase(Purchase $purchase): static
    {
        // set the owning side of the relation if necessary
        if ($purchase->getItem() !== $this) {
            $purchase->setItem($this);
        }

        $this->purchase = $purchase;

        return $this;
    }
}