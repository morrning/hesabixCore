<?php

namespace App\Entity;

use App\Repository\PrintItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrintItemRepository::class)]
class PrintItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'printItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Printer $printer = null;

    #[ORM\Column(length: 255)]
    private ?string $file = null;

    #[ORM\Column(nullable: true)]
    private ?bool $printed = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrinter(): ?Printer
    {
        return $this->printer;
    }

    public function setPrinter(?Printer $printer): static
    {
        $this->printer = $printer;

        return $this;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(string $file): static
    {
        $this->file = $file;

        return $this;
    }

    public function isPrinted(): ?bool
    {
        return $this->printed;
    }

    public function setPrinted(?bool $printed): static
    {
        $this->printed = $printed;

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
}
