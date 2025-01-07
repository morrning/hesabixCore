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

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $des = null;

    #[ORM\ManyToOne]
    private ?Person $person = null;

    #[ORM\ManyToOne]
    private ?BankAccount $bank = null;

    #[ORM\ManyToOne]
    private ?Cashdesk $cashdesk = null;

    #[ORM\ManyToOne]
    private ?Salary $salary = null;

    #[ORM\ManyToOne(inversedBy: 'preInvoiceItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Business $bid = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Year $year = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $discount = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tax = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?HesabdariTable $refID = null;

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

    public function getDes(): ?string
    {
        return $this->des;
    }

    public function setDes(?string $des): static
    {
        $this->des = $des;

        return $this;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): static
    {
        $this->person = $person;

        return $this;
    }

    public function getBank(): ?BankAccount
    {
        return $this->bank;
    }

    public function setBank(?BankAccount $bank): static
    {
        $this->bank = $bank;

        return $this;
    }

    public function getCashdesk(): ?Cashdesk
    {
        return $this->cashdesk;
    }

    public function setCashdesk(?Cashdesk $cashdesk): static
    {
        $this->cashdesk = $cashdesk;

        return $this;
    }

    public function getSalary(): ?Salary
    {
        return $this->salary;
    }

    public function setSalary(?Salary $salary): static
    {
        $this->salary = $salary;

        return $this;
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

    public function getYear(): ?Year
    {
        return $this->year;
    }

    public function setYear(?Year $year): static
    {
        $this->year = $year;

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

    public function getRefID(): ?HesabdariTable
    {
        return $this->refID;
    }

    public function setRefID(?HesabdariTable $refID): static
    {
        $this->refID = $refID;

        return $this;
    }
}
