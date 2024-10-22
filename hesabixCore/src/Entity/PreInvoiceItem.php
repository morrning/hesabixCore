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
}
