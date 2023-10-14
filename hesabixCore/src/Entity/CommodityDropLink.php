<?php

namespace App\Entity;

use App\Repository\CommodityDropLinkRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommodityDropLinkRepository::class)]
class CommodityDropLink
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'commodityDropLinks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CommodityDrop $commoditydrop = null;

    #[ORM\ManyToOne(inversedBy: 'commodityDropLinks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Commodity $commodity = null;

    #[ORM\Column(length: 255)]
    private ?string $value = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommoditydrop(): ?CommodityDrop
    {
        return $this->commoditydrop;
    }

    public function setCommoditydrop(?CommodityDrop $commoditydrop): static
    {
        $this->commoditydrop = $commoditydrop;

        return $this;
    }

    public function getCommodity(): ?Commodity
    {
        return $this->commodity;
    }

    public function setCommodity(?Commodity $commodity): static
    {
        $this->commodity = $commodity;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }
}
