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

    #[ORM\Column(nullable: true)]
    private ?bool $sellChart = null;

    #[ORM\Column(nullable: true)]
    private ?bool $topCommoditiesChart = null;

    #[ORM\Column(nullable: true)]
    private ?bool $costs = null;

    #[ORM\Column(nullable: true)]
    private ?bool $topCostCenters = null;

    #[ORM\Column(nullable: true)]
    private ?bool $incomes = null;

    #[ORM\Column(nullable: true)]
    private ?bool $topIncomesChart = null;

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

    public function isSellChart(): ?bool
    {
        return $this->sellChart;
    }

    public function setSellChart(?bool $sellChart): static
    {
        $this->sellChart = $sellChart;

        return $this;
    }

    public function isTopCommoditiesChart(): ?bool
    {
        return $this->topCommoditiesChart;
    }

    public function setTopCommoditiesChart(?bool $topCommoditiesChart): static
    {
        $this->topCommoditiesChart = $topCommoditiesChart;

        return $this;
    }

    public function isCosts(): ?bool
    {
        return $this->costs;
    }

    public function setCosts(?bool $costs): static
    {
        $this->costs = $costs;

        return $this;
    }

    public function isTopCostCenters(): ?bool
    {
        return $this->topCostCenters;
    }

    public function setTopCostCenters(?bool $topCostCenters): static
    {
        $this->topCostCenters = $topCostCenters;

        return $this;
    }

    public function isIncomes(): ?bool
    {
        return $this->incomes;
    }

    public function setIncomes(?bool $incomes): static
    {
        $this->incomes = $incomes;

        return $this;
    }

    public function isTopIncomesChart(): ?bool
    {
        return $this->topIncomesChart;
    }

    public function setTopIncomesChart(?bool $topIncomesChart): static
    {
        $this->topIncomesChart = $topIncomesChart;

        return $this;
    }
}
