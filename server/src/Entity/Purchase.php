<?php

namespace App\Entity;

use App\Repository\PurchaseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PurchaseRepository::class)]
class Purchase
{
    #[ORM\Id]
    #[ORM\OneToOne(inversedBy: 'purchase', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Item $item = null;

    #[ORM\Column(length: 255)]
    private ?string $purchaseProof = null;

    #[ORM\Column(length: 255)]
    private ?string $congratulatoryText = null;

    #[ORM\ManyToOne(inversedBy: 'purchases')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $buyer = null;

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(Item $item): static
    {
        $this->item = $item;

        return $this;
    }

    public function getPurchaseProof(): ?string
    {
        return $this->purchaseProof;
    }

    public function setPurchaseProof(string $purchaseProof): static
    {
        $this->purchaseProof = $purchaseProof;

        return $this;
    }

    public function getCongratulatoryText(): ?string
    {
        return $this->congratulatoryText;
    }

    public function setCongratulatoryText(string $congratulatoryText): static
    {
        $this->congratulatoryText = $congratulatoryText;

        return $this;
    }

    public function getBuyer(): ?User
    {
        return $this->buyer;
    }

    public function setBuyer(?User $buyer): static
    {
        $this->buyer = $buyer;

        return $this;
    }
}
