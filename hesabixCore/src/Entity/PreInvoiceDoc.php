<?php

namespace App\Entity;

use App\Repository\PreInvoiceDocRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PreInvoiceDocRepository::class)]
class PreInvoiceDoc
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'preInvoiceDocs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $submitter = null;

    #[ORM\Column(length: 100)]
    private ?string $code = null;

    #[ORM\ManyToOne(inversedBy: 'preInvoiceDocs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Person $person = null;

    #[ORM\ManyToOne(inversedBy: 'preInvoiceDocs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Business $bid = null;

    #[ORM\ManyToOne(inversedBy: 'preInvoiceDocs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Money $money = null;

    #[ORM\ManyToOne(inversedBy: 'preInvoiceDocs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Year $year = null;

    #[ORM\Column(length: 80)]
    private ?string $date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $des = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $amount = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mdate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $plugin = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $refData = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shortlink = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'preInvoiceDocs')]
    private ?InvoiceType $invoiceLabel = null;

    #[ORM\ManyToOne(inversedBy: 'preinvoiceDocsSalemans')]
    private ?Person $salesman = null;

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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

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

    public function getBid(): ?Business
    {
        return $this->bid;
    }

    public function setBid(?Business $bid): static
    {
        $this->bid = $bid;

        return $this;
    }

    public function getMoney(): ?Money
    {
        return $this->money;
    }

    public function setMoney(?Money $money): static
    {
        $this->money = $money;

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

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(string $date): static
    {
        $this->date = $date;

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

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(?string $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getMdate(): ?string
    {
        return $this->mdate;
    }

    public function setMdate(?string $mdate): static
    {
        $this->mdate = $mdate;

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

    public function getRefData(): ?string
    {
        return $this->refData;
    }

    public function setRefData(?string $refData): static
    {
        $this->refData = $refData;

        return $this;
    }

    public function getShortlink(): ?string
    {
        return $this->shortlink;
    }

    public function setShortlink(?string $shortlink): static
    {
        $this->shortlink = $shortlink;

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

    public function getInvoiceLabel(): ?InvoiceType
    {
        return $this->invoiceLabel;
    }

    public function setInvoiceLabel(?InvoiceType $invoiceLabel): static
    {
        $this->invoiceLabel = $invoiceLabel;

        return $this;
    }

    public function getSalesman(): ?Person
    {
        return $this->salesman;
    }

    public function setSalesman(?Person $salesman): static
    {
        $this->salesman = $salesman;

        return $this;
    }
}
