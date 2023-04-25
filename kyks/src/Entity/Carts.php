<?php

namespace App\Entity;

use App\Repository\CartsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CartsRepository::class)]
class Carts
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?user $userId = null;

    #[ORM\ManyToMany(targetEntity: products::class)]
    private Collection $productId;

    public function __construct()
    {
        $this->productId = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?user
    {
        return $this->userId;
    }

    public function setUserId(user $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return Collection<int, products>
     */
    public function getProductId(): Collection
    {
        return $this->productId;
    }

    public function addProductId(products $productId): self
    {
        if (!$this->productId->contains($productId)) {
            $this->productId->add($productId);
        }

        return $this;
    }

    public function removeProductId(products $productId): self
    {
        $this->productId->removeElement($productId);

        return $this;
    }
}
