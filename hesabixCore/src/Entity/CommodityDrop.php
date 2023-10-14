<?php

namespace App\Entity;

use App\Repository\CommodityDropRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommodityDropRepository::class)]
class CommodityDrop
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'commodityDrops')]
    private ?Business $bid = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $UDPrice = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $UDPricePercent = null;

    #[ORM\OneToMany(mappedBy: 'commoditydrop', targetEntity: CommodityDropLink::class, orphanRemoval: true)]
    private Collection $commodityDropLinks;

    #[ORM\Column(nullable: true)]
    private ?bool $canEdit = null;

    public function __construct()
    {
        $this->commodityDropLinks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBid(): ?Business
    {
        return $this->bid;
    }

    public function setBid(?Business $bid): static
    {
        $this->bid = $bid;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getUDPrice(): ?string
    {
        return $this->UDPrice;
    }

    public function setUDPrice(?string $UDPrice): static
    {
        $this->UDPrice = $UDPrice;

        return $this;
    }

    public function getUDPricePercent(): ?string
    {
        return $this->UDPricePercent;
    }

    public function setUDPricePercent(?string $UDPricePercent): static
    {
        $this->UDPricePercent = $UDPricePercent;

        return $this;
    }

    /**
     * @return Collection<int, CommodityDropLink>
     */
    public function getCommodityDropLinks(): Collection
    {
        return $this->commodityDropLinks;
    }

    public function addCommodityDropLink(CommodityDropLink $commodityDropLink): static
    {
        if (!$this->commodityDropLinks->contains($commodityDropLink)) {
            $this->commodityDropLinks->add($commodityDropLink);
            $commodityDropLink->setCommoditydrop($this);
        }

        return $this;
    }

    public function removeCommodityDropLink(CommodityDropLink $commodityDropLink): static
    {
        if ($this->commodityDropLinks->removeElement($commodityDropLink)) {
            // set the owning side to null (unless already changed)
            if ($commodityDropLink->getCommoditydrop() === $this) {
                $commodityDropLink->setCommoditydrop(null);
            }
        }

        return $this;
    }

    public function isCanEdit(): ?bool
    {
        return $this->canEdit;
    }

    public function setCanEdit(?bool $canEdit): static
    {
        $this->canEdit = $canEdit;

        return $this;
    }
}
