<?php

namespace App\Entity;

use App\Repository\PrintTemplateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrintTemplateRepository::class)]
class PrintTemplate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'printTemplates')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Business $bid = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $fastSellInvoice = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $cashdeskTicket = null;

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

    public function getFastSellInvoice(): ?string
    {
        return $this->fastSellInvoice;
    }

    public function setFastSellInvoice(?string $fastSellInvoice): static
    {
        $this->fastSellInvoice = $fastSellInvoice;

        return $this;
    }

    public function getCashdeskTicket(): ?string
    {
        return $this->cashdeskTicket;
    }

    public function setCashdeskTicket(?string $cashdeskTicket): static
    {
        $this->cashdeskTicket = $cashdeskTicket;

        return $this;
    }
}
