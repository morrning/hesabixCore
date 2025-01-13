<?php

namespace App\Entity;

use App\Repository\DashboardSettingsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DashboardSettingsRepository::class)]
class DashboardSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'dashboardSettings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $submitter = null;

    #[ORM\ManyToOne(inversedBy: 'dashboardSettings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Business $bid = null;

    #[ORM\Column(nullable: true)]
    private ?bool $wallet = null;

    #[ORM\Column(nullable: true)]
    private ?bool $banks = null;

    #[ORM\Column(nullable: true)]
    private ?bool $accDocs = null;

    #[ORM\Column(nullable: true)]
    private ?bool $commodities = null;

    #[ORM\Column(nullable: true)]
    private ?bool $persons = null;

    #[ORM\Column(nullable: true)]
    private ?bool $buys = null;

    #[ORM\Column(nullable: true)]
    private ?bool $sells = null;

    #[ORM\Column(nullable: true)]
    private ?bool $accountingTotal = null;

    #[ORM\Column(nullable: true)]
    private ?bool $notif = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getBid(): ?Business
    {
        return $this->bid;
    }

    public function setBid(?Business $bid): static
    {
        $this->bid = $bid;

        return $this;
    }

    public function isWallet(): ?bool
    {
        return $this->wallet;
    }

    public function setWallet(?bool $wallet): static
    {
        $this->wallet = $wallet;

        return $this;
    }

    public function isBanks(): ?bool
    {
        return $this->banks;
    }

    public function setBanks(?bool $banks): static
    {
        $this->banks = $banks;

        return $this;
    }

    public function isAccDocs(): ?bool
    {
        return $this->accDocs;
    }

    public function setAccDocs(?bool $accDocs): static
    {
        $this->accDocs = $accDocs;

        return $this;
    }

    public function isCommodities(): ?bool
    {
        return $this->commodities;
    }

    public function setCommodities(?bool $commodities): static
    {
        $this->commodities = $commodities;

        return $this;
    }

    public function isPersons(): ?bool
    {
        return $this->persons;
    }

    public function setPersons(?bool $persons): static
    {
        $this->persons = $persons;

        return $this;
    }

    public function isBuys(): ?bool
    {
        return $this->buys;
    }

    public function setBuys(?bool $buys): static
    {
        $this->buys = $buys;

        return $this;
    }

    public function isSells(): ?bool
    {
        return $this->sells;
    }

    public function setSells(?bool $sells): static
    {
        $this->sells = $sells;

        return $this;
    }

    public function isAccountingTotal(): ?bool
    {
        return $this->accountingTotal;
    }

    public function setAccountingTotal(?bool $accountingTotal): static
    {
        $this->accountingTotal = $accountingTotal;

        return $this;
    }

    public function isNotif(): ?bool
    {
        return $this->notif;
    }

    public function setNotif(?bool $notif): static
    {
        $this->notif = $notif;

        return $this;
    }
}
