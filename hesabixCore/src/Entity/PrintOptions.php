<?php

namespace App\Entity;

use App\Repository\PrintOptionsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrintOptionsRepository::class)]
class PrintOptions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'printOptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Business $bid = null;

    #[ORM\Column(nullable: true)]
    private ?bool $sellBidInfo = null;

    #[ORM\Column(nullable: true)]
    private ?bool $sellNote = null;

    #[ORM\Column(nullable: true)]
    private ?bool $sellTaxInfo = null;

    #[ORM\Column(nullable: true)]
    private ?bool $sellDiscountInfo = null;

    #[ORM\Column(nullable: true)]
    private ?bool $sellPays = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $sellNoteString = null;

    #[ORM\Column]
    private ?bool $buyBidInfo = null;

    #[ORM\Column(nullable: true)]
    private ?bool $buyTaxInfo = null;

    #[ORM\Column(nullable: true)]
    private ?bool $buyDiscountInfo = null;

    #[ORM\Column(nullable: true)]
    private ?bool $buyNote = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $buyNoteString = null;

    #[ORM\Column(nullable: true)]
    private ?bool $buyPays = null;

    #[ORM\Column(nullable: true)]
    private ?bool $RfbuyBidInfo = null;

    #[ORM\Column(nullable: true)]
    private ?bool $RfbuyTaxInfo = null;

    #[ORM\Column(nullable: true)]
    private ?bool $RfbuyDiscountInfo = null;

    #[ORM\Column(nullable: true)]
    private ?bool $RfbuyNote = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $RfbuyNoteString = null;

    #[ORM\Column(nullable: true)]
    private ?bool $RfBuyPays = null;

    #[ORM\Column(nullable: true)]
    private ?bool $RfsellBidInfo = null;

    #[ORM\Column(nullable: true)]
    private ?bool $RfsellTaxInfo = null;

    #[ORM\Column(nullable: true)]
    private ?bool $RfsellDiscountInfo = null;

    #[ORM\Column(nullable: true)]
    private ?bool $RfsellNote = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $RfsellNoteString = null;

    #[ORM\Column(nullable: true)]
    private ?bool $RfsellPays = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sellPaper = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $buyPaper = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $rfbuyPaper = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $rfsellPaper = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $repserviceNoteString = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $paper = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $repservicePaper = null;

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

    public function isSellBidInfo(): ?bool
    {
        return $this->sellBidInfo;
    }

    public function setSellBidInfo(?bool $sellBidInfo): static
    {
        $this->sellBidInfo = $sellBidInfo;

        return $this;
    }

    public function isSellNote(): ?bool
    {
        return $this->sellNote;
    }

    public function setSellNote(?bool $sellNote): static
    {
        $this->sellNote = $sellNote;

        return $this;
    }

    public function isSellTaxInfo(): ?bool
    {
        return $this->sellTaxInfo;
    }

    public function setSellTaxInfo(?bool $sellTaxInfo): static
    {
        $this->sellTaxInfo = $sellTaxInfo;

        return $this;
    }

    public function isSellDiscountInfo(): ?bool
    {
        return $this->sellDiscountInfo;
    }

    public function setSellDiscountInfo(?bool $sellDiscountInfo): static
    {
        $this->sellDiscountInfo = $sellDiscountInfo;

        return $this;
    }

    public function isSellPays(): ?bool
    {
        return $this->sellPays;
    }

    public function setSellPays(?bool $sellPays): static
    {
        $this->sellPays = $sellPays;

        return $this;
    }

    public function getSellNoteString(): ?string
    {
        return $this->sellNoteString;
    }

    public function setSellNoteString(?string $sellNoteString): static
    {
        $this->sellNoteString = $sellNoteString;

        return $this;
    }

    public function isBuyBidInfo(): ?bool
    {
        return $this->buyBidInfo;
    }

    public function setBuyBidInfo(bool $buyBidInfo): static
    {
        $this->buyBidInfo = $buyBidInfo;

        return $this;
    }

    public function isBuyTaxInfo(): ?bool
    {
        return $this->buyTaxInfo;
    }

    public function setBuyTaxInfo(?bool $buyTaxInfo): static
    {
        $this->buyTaxInfo = $buyTaxInfo;

        return $this;
    }

    public function isBuyDiscountInfo(): ?bool
    {
        return $this->buyDiscountInfo;
    }

    public function setBuyDiscountInfo(?bool $buyDiscountInfo): static
    {
        $this->buyDiscountInfo = $buyDiscountInfo;

        return $this;
    }

    public function isBuyNote(): ?bool
    {
        return $this->buyNote;
    }

    public function setBuyNote(?bool $buyNote): static
    {
        $this->buyNote = $buyNote;

        return $this;
    }

    public function getBuyNoteString(): ?string
    {
        return $this->buyNoteString;
    }

    public function setBuyNoteString(?string $buyNoteString): static
    {
        $this->buyNoteString = $buyNoteString;

        return $this;
    }

    public function isBuyPays(): ?bool
    {
        return $this->buyPays;
    }

    public function setBuyPays(?bool $buyPays): static
    {
        $this->buyPays = $buyPays;

        return $this;
    }

    public function isRfbuyBidInfo(): ?bool
    {
        return $this->RfbuyBidInfo;
    }

    public function setRfbuyBidInfo(?bool $RfbuyBidInfo): static
    {
        $this->RfbuyBidInfo = $RfbuyBidInfo;

        return $this;
    }

    public function isRfbuyTaxInfo(): ?bool
    {
        return $this->RfbuyTaxInfo;
    }

    public function setRfbuyTaxInfo(?bool $RfbuyTaxInfo): static
    {
        $this->RfbuyTaxInfo = $RfbuyTaxInfo;

        return $this;
    }

    public function isRfbuyDiscountInfo(): ?bool
    {
        return $this->RfbuyDiscountInfo;
    }

    public function setRfbuyDiscountInfo(?bool $RfbuyDiscountInfo): static
    {
        $this->RfbuyDiscountInfo = $RfbuyDiscountInfo;

        return $this;
    }

    public function isRfbuyNote(): ?bool
    {
        return $this->RfbuyNote;
    }

    public function setRfbuyNote(?bool $RfbuyNote): static
    {
        $this->RfbuyNote = $RfbuyNote;

        return $this;
    }

    public function getRfbuyNoteString(): ?string
    {
        return $this->RfbuyNoteString;
    }

    public function setRfbuyNoteString(?string $RfbuyNoteString): static
    {
        $this->RfbuyNoteString = $RfbuyNoteString;

        return $this;
    }

    public function isRfBuyPays(): ?bool
    {
        return $this->RfBuyPays;
    }

    public function setRfBuyPays(?bool $RfBuyPays): static
    {
        $this->RfBuyPays = $RfBuyPays;

        return $this;
    }

    public function isRfsellBidInfo(): ?bool
    {
        return $this->RfsellBidInfo;
    }

    public function setRfsellBidInfo(?bool $RfsellBidInfo): static
    {
        $this->RfsellBidInfo = $RfsellBidInfo;

        return $this;
    }

    public function isRfsellTaxInfo(): ?bool
    {
        return $this->RfsellTaxInfo;
    }

    public function setRfsellTaxInfo(?bool $RfsellTaxInfo): static
    {
        $this->RfsellTaxInfo = $RfsellTaxInfo;

        return $this;
    }

    public function isRfsellDiscountInfo(): ?bool
    {
        return $this->RfsellDiscountInfo;
    }

    public function setRfsellDiscountInfo(?bool $RfsellDiscountInfo): static
    {
        $this->RfsellDiscountInfo = $RfsellDiscountInfo;

        return $this;
    }

    public function isRfsellNote(): ?bool
    {
        return $this->RfsellNote;
    }

    public function setRfsellNote(?bool $RfsellNote): static
    {
        $this->RfsellNote = $RfsellNote;

        return $this;
    }

    public function getRfsellNoteString(): ?string
    {
        return $this->RfsellNoteString;
    }

    public function setRfsellNoteString(?string $RfsellNoteString): static
    {
        $this->RfsellNoteString = $RfsellNoteString;

        return $this;
    }

    public function isRfsellPays(): ?bool
    {
        return $this->RfsellPays;
    }

    public function setRfsellPays(?bool $RfsellPays): static
    {
        $this->RfsellPays = $RfsellPays;

        return $this;
    }

    public function getSellPaper(): ?string
    {
        return $this->sellPaper;
    }

    public function setSellPaper(?string $sellPaper): static
    {
        $this->sellPaper = $sellPaper;

        return $this;
    }

    public function getBuyPaper(): ?string
    {
        return $this->buyPaper;
    }

    public function setBuyPaper(?string $buyPaper): static
    {
        $this->buyPaper = $buyPaper;

        return $this;
    }

    public function getRfbuyPaper(): ?string
    {
        return $this->rfbuyPaper;
    }

    public function setRfbuyPaper(?string $rfbuyPaper): static
    {
        $this->rfbuyPaper = $rfbuyPaper;

        return $this;
    }

    public function getRfsellPaper(): ?string
    {
        return $this->rfsellPaper;
    }

    public function setRfsellPaper(?string $rfsellPaper): static
    {
        $this->rfsellPaper = $rfsellPaper;

        return $this;
    }

    public function getRepserviceNoteString(): ?string
    {
        return $this->repserviceNoteString;
    }

    public function setRepserviceNoteString(?string $repserviceNoteString): static
    {
        $this->repserviceNoteString = $repserviceNoteString;

        return $this;
    }

    public function getPaper(): ?string
    {
        return $this->paper;
    }

    public function setPaper(?string $paper): static
    {
        $this->paper = $paper;

        return $this;
    }

    public function getRepservicePaper(): ?string
    {
        return $this->repservicePaper;
    }

    public function setRepservicePaper(?string $repservicePaper): static
    {
        $this->repservicePaper = $repservicePaper;

        return $this;
    }
}
