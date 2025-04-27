<?php

namespace App\Entity;

use App\Repository\HesabdariRowRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: HesabdariRowRepository::class)]
class HesabdariRow
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'hesabdariRows')]
    #[ORM\JoinColumn(nullable: false)]
    #[Ignore]
    private ?HesabdariDoc $doc = null;

    #[ORM\Column(length: 255)]
    private ?string $bs = null;

    #[ORM\Column(length: 255)]
    private ?string $bd = null;

    #[ORM\ManyToOne(inversedBy: 'hesabdariRows')]
    #[ORM\JoinColumn(nullable: false)]
    #[Ignore]
    private ?HesabdariTable $ref = null;

    #[ORM\ManyToOne(inversedBy: 'hesabdariRows')]
    #[Ignore]
    private ?Person $person = null;

    #[ORM\ManyToOne(inversedBy: 'hesabdariRows')]
    #[Ignore]
    private ?BankAccount $bank = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $des = null;

    #[ORM\ManyToOne(inversedBy: 'hesabdariRows')]
    #[ORM\JoinColumn(nullable: false)]
    #[Ignore]
    private ?Business $bid = null;

    #[ORM\ManyToOne(inversedBy: 'hesabdariRows')]
    #[ORM\JoinColumn(nullable: false)]
    #[Ignore]
    private ?Year $year = null;

    #[ORM\ManyToOne(inversedBy: 'hesabdariRows')]
    #[Ignore]
    private ?Commodity $commodity = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?int $commdityCount = null;

    #[ORM\ManyToOne(inversedBy: 'hesabdariRows')]
    #[Ignore]
    private ?Salary $salary = null;

    #[ORM\ManyToOne(inversedBy: 'hesabdariRows')]
    #[Ignore]
    private ?Cashdesk $cashdesk = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $referral = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $refData = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $plugin = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $tempData = null;

    #[ORM\ManyToOne(inversedBy: 'hesabdariRows')]
    private ?Cheque $cheque = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $discount = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tax = null;

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDoc(): ?HesabdariDoc
    {
        return $this->doc;
    }

    public function setDoc(?HesabdariDoc $doc): self
    {
        $this->doc = $doc;

        return $this;
    }

    public function getBs(): ?string
    {
        return $this->bs;
    }

    public function setBs(string $bs): self
    {
        $this->bs = $bs;

        return $this;
    }

    public function getBd(): ?string
    {
        return $this->bd;
    }

    public function setBd(string $bd): self
    {
        $this->bd = $bd;

        return $this;
    }

    public function getRef(): ?HesabdariTable
    {
        return $this->ref;
    }

    public function setRef(?HesabdariTable $ref): self
    {
        $this->ref = $ref;

        return $this;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): self
    {
        $this->person = $person;

        return $this;
    }

    public function getBank(): ?BankAccount
    {
        return $this->bank;
    }

    public function setBank(?BankAccount $bank): self
    {
        $this->bank = $bank;

        return $this;
    }

    public function getDes(): ?string
    {
        return $this->des;
    }

    public function setDes(?string $des): self
    {
        $this->des = $des;

        return $this;
    }

    public function getBid(): ?Business
    {
        return $this->bid;
    }

    public function setBid(?Business $bid): self
    {
        $this->bid = $bid;

        return $this;
    }

    public function getYear(): ?Year
    {
        return $this->year;
    }

    public function setYear(?Year $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getCommodity(): ?Commodity
    {
        return $this->commodity;
    }

    public function setCommodity(?Commodity $commodity): self
    {
        $this->commodity = $commodity;

        return $this;
    }

    public function getCommdityCount(): ?int
    {
        return $this->commdityCount;
    }

    public function setCommdityCount(?int $commdityCount): self
    {
        $this->commdityCount = $commdityCount;

        return $this;
    }

    public function getSalary(): ?Salary
    {
        return $this->salary;
    }

    public function setSalary(?Salary $salary): self
    {
        $this->salary = $salary;

        return $this;
    }

    public function getCashdesk(): ?Cashdesk
    {
        return $this->cashdesk;
    }

    public function setCashdesk(?Cashdesk $cashdesk): self
    {
        $this->cashdesk = $cashdesk;

        return $this;
    }

    public function getReferral(): ?string
    {
        return $this->referral;
    }

    public function setReferral(?string $referral): self
    {
        $this->referral = $referral;

        return $this;
    }

    public function getRefData(): ?string
    {
        return $this->refData;
    }

    public function setRefData(?string $refData): static
    {
        $this->refData = $refData;

        return $this;
    }

    public function getPlugin(): ?string
    {
        return $this->plugin;
    }

    public function setPlugin(?string $plugin): static
    {
        $this->plugin = $plugin;

        return $this;
    }

    public function getTempData(): ?array
    {
        return $this->tempData;
    }

    public function setTempData(?array $tempData): static
    {
        $this->tempData = $tempData;

        return $this;
    }

    public function getCheque(): ?Cheque
    {
        return $this->cheque;
    }

    public function setCheque(?Cheque $cheque): static
    {
        $this->cheque = $cheque;

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
}