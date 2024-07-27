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
}
