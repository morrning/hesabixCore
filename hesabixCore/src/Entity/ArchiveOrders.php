<?php

namespace App\Entity;

use App\Repository\ArchiveOrdersRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArchiveOrdersRepository::class)]
class ArchiveOrders
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'archiveOrders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Business $bid = null;

    #[ORM\ManyToOne(inversedBy: 'archiveOrders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $submitter = null;

    #[ORM\Column(length: 255)]
    private ?string $dateSubmit = null;

    #[ORM\Column(length: 255)]
    private ?string $orderSize = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $gatePay = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $price = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $verifyCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $refID = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cardPan = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ExpireDate = null;

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

    public function getOrderSize(): ?string
    {
        return $this->orderSize;
    }

    public function setOrderSize(string $orderSize): static
    {
        $this->orderSize = $orderSize;

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

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): static
    {
        $this->price = $price;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

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

    public function getCardPan(): ?string
    {
        return $this->cardPan;
    }

    public function setCardPan(?string $cardPan): static
    {
        $this->cardPan = $cardPan;

        return $this;
    }

    public function getExpireDate(): ?string
    {
        return $this->ExpireDate;
    }

    public function setExpireDate(?string $ExpireDate): static
    {
        $this->ExpireDate = $ExpireDate;

        return $this;
    }
}
