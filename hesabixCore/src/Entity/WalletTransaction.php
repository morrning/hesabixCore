<?php

namespace App\Entity;

use App\Repository\WalletTransactionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WalletTransactionRepository::class)]
class WalletTransaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'walletTransactions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Business $bid = null;

    #[ORM\Column(length: 255)]
    private ?string $dateSubmit = null;

    #[ORM\ManyToOne(inversedBy: 'walletTransactions')]
    private ?User $submitter = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    private ?string $amount = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shaba = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bank = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cardNum = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $des = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cardPan = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $refID = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $verifyCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $gatePay = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $transactionID = null;

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

    public function getDateSubmit(): ?string
    {
        return $this->dateSubmit;
    }

    public function setDateSubmit(string $dateSubmit): static
    {
        $this->dateSubmit = $dateSubmit;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

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

    public function getShaba(): ?string
    {
        return $this->shaba;
    }

    public function setShaba(?string $shaba): static
    {
        $this->shaba = $shaba;

        return $this;
    }

    public function getBank(): ?string
    {
        return $this->bank;
    }

    public function setBank(?string $bank): static
    {
        $this->bank = $bank;

        return $this;
    }

    public function getCardNum(): ?string
    {
        return $this->cardNum;
    }

    public function setCardNum(?string $cardNum): static
    {
        $this->cardNum = $cardNum;

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

    public function getDes(): ?string
    {
        return $this->des;
    }

    public function setDes(?string $des): static
    {
        $this->des = $des;

        return $this;
    }

    public function getCardPan(): ?string
    {
        return $this->cardPan;
    }

    public function setCardPan(?string $cardPan): static
    {
        $this->cardPan = $cardPan;

        return $this;
    }

    public function getRefID(): ?string
    {
        return $this->refID;
    }

    public function setRefID(?string $refID): static
    {
        $this->refID = $refID;

        return $this;
    }

    public function getVerifyCode(): ?string
    {
        return $this->verifyCode;
    }

    public function setVerifyCode(?string $verifyCode): static
    {
        $this->verifyCode = $verifyCode;

        return $this;
    }

    public function getGatePay(): ?string
    {
        return $this->gatePay;
    }

    public function setGatePay(?string $gatePay): static
    {
        $this->gatePay = $gatePay;

        return $this;
    }

    public function getTransactionID(): ?string
    {
        return $this->transactionID;
    }

    public function setTransactionID(?string $transactionID): static
    {
        $this->transactionID = $transactionID;

        return $this;
    }
}
