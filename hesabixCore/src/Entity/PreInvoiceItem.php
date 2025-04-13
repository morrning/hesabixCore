<?php

namespace App\Entity;

use App\Repository\PreInvoiceItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PreInvoiceItemRepository::class)]
class PreInvoiceItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'preInvoiceItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Commodity $commodity = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $commodityCount = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $bs = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $bd = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $discountPercent = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $discountAmount = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $des = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $discount = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tax = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $showPercentDiscount = null;

    #[ORM\ManyToOne(inversedBy: 'preInvoiceItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PreInvoiceDoc $doc = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCommodityCount(): ?string
    {
        return $this->commodityCount;
    }

    public function setCommodityCount(?string $commodityCount): static
    {
        $this->commodityCount = $commodityCount;

        return $this;
    }

    public function getBs(): ?string
    {
        return $this->bs;
    }

    public function setBs(?string $bs): static
    {
        $this->bs = $bs;

        return $this;
    }

    public function getBd(): ?string
    {
        return $this->bd;
    }

    public function setBd(?string $bd): static
    {
        $this->bd = $bd;

        return $this;
    }

    public function getDiscountPercent(): ?string
    {
        return $this->discountPercent;
    }

    public function setDiscountPercent(?string $discountPercent): static
    {
        $this->discountPercent = $discountPercent;
        return $this;
    }

    public function getDiscountAmount(): ?string
    {
        return $this->discountAmount;
    }

    public function setDiscountAmount(?string $discountAmount): static
    {
        $this->discountAmount = $discountAmount;
        return $this;
    }

    public function getDes(): ?string
    {
        return $this->des;
    }

    public function setDes(?string $des): static
    {
        $this->des = $des;

        return $this;
    }

    public function getDiscount(): ?string
    {
        return $this->discount;
    }

    public function setDiscount(?string $discount): static
    {
        $this->discount = $discount;

        return $this;
    }

    public function getTax(): ?string
    {
        return $this->tax;
    }

    public function setTax(?string $tax): static
    {
        $this->tax = $tax;

        return $this;
    }

    public function isShowPercentDiscount(): ?bool
    {
        return $this->showPercentDiscount;
    }

    public function setShowPercentDiscount(?bool $showPercentDiscount): static
    {
        $this->showPercentDiscount = $showPercentDiscount;
        return $this;
    }

    public function getDoc(): ?PreInvoiceDoc
    {
        return $this->doc;
    }

    public function setDoc(?PreInvoiceDoc $doc): static
    {
        $this->doc = $doc;

        return $this;
    }
}
