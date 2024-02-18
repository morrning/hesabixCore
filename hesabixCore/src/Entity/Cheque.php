<?php

namespace App\Entity;

use App\Repository\ChequeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChequeRepository::class)]
class Cheque
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'cheques')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Business $bid = null;

    #[ORM\ManyToOne(inversedBy: 'cheques')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $submitter = null;

    #[ORM\Column(length: 50)]
    private ?string $dateSubmit = null;

    #[ORM\Column(length: 20)]
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'cheques')]
    private ?BankAccount $bank = null;

    #[ORM\ManyToOne(inversedBy: 'cheques')]
    private ?Person $person = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $sayadNum = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $des = null;

    #[ORM\Column(length: 50)]
    private ?string $dateStamp = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $payDate = null;

    #[ORM\ManyToOne]
    private ?HesabdariTable $ref = null;

    #[ORM\Column(length: 255)]
    private ?string $number = null;

    #[ORM\OneToMany(mappedBy: 'cheque', targetEntity: HesabdariRow::class)]
    private Collection $hesabdariRows;

    #[ORM\Column(length: 255)]
    private ?string $bankOncheque = null;

    #[ORM\Column(length: 255)]
    private ?string $amount = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(nullable: true)]
    private ?bool $locked = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $date = null;

    public function __construct()
    {
        $this->hesabdariRows = new ArrayCollection();
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

    public function getSubmitter(): ?User
    {
        return $this->submitter;
    }

    public function setSubmitter(?User $submitter): static
    {
        $this->submitter = $submitter;

        return $this;
    }

    public function getDateSubmit(): ?string
    {
        return $this->dateSubmit;
    }

    public function setDateSubmit(string $dateSubmit): static
    {
        $this->dateSubmit = $dateSubmit;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

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

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): static
    {
        $this->person = $person;

        return $this;
    }

    public function getSayadNum(): ?string
    {
        return $this->sayadNum;
    }

    public function setSayadNum(?string $sayadNum): static
    {
        $this->sayadNum = $sayadNum;

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

    public function getDateStamp(): ?string
    {
        return $this->dateStamp;
    }

    public function setDateStamp(string $dateStamp): static
    {
        $this->dateStamp = $dateStamp;

        return $this;
    }

    public function getPayDate(): ?string
    {
        return $this->payDate;
    }

    public function setPayDate(?string $payDate): static
    {
        $this->payDate = $payDate;

        return $this;
    }

    public function getRef(): ?HesabdariTable
    {
        return $this->ref;
    }

    public function setRef(?HesabdariTable $ref): static
    {
        $this->ref = $ref;

        return $this;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): static
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @return Collection<int, HesabdariRow>
     */
    public function getHesabdariRows(): Collection
    {
        return $this->hesabdariRows;
    }

    public function addHesabdariRow(HesabdariRow $hesabdariRow): static
    {
        if (!$this->hesabdariRows->contains($hesabdariRow)) {
            $this->hesabdariRows->add($hesabdariRow);
            $hesabdariRow->setCheque($this);
        }

        return $this;
    }

    public function removeHesabdariRow(HesabdariRow $hesabdariRow): static
    {
        if ($this->hesabdariRows->removeElement($hesabdariRow)) {
            // set the owning side to null (unless already changed)
            if ($hesabdariRow->getCheque() === $this) {
                $hesabdariRow->setCheque(null);
            }
        }

        return $this;
    }

    public function getBankOncheque(): ?string
    {
        return $this->bankOncheque;
    }

    public function setBankOncheque(string $bankOncheque): static
    {
        $this->bankOncheque = $bankOncheque;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function isLocked(): ?bool
    {
        return $this->locked;
    }

    public function setLocked(?bool $locked): static
    {
        $this->locked = $locked;

        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(?string $date): static
    {
        $this->date = $date;

        return $this;
    }

}
