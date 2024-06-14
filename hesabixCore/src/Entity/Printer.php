<?php

namespace App\Entity;

use App\Repository\PrinterRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrinterRepository::class)]
class Printer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'printers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Business $bid = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $token = null;

    #[ORM\OneToMany(mappedBy: 'printer', targetEntity: PrintItem::class, orphanRemoval: true)]
    private Collection $printItems;

    public function __construct()
    {
        $this->printItems = new ArrayCollection();
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return Collection<int, PrintItem>
     */
    public function getPrintItems(): Collection
    {
        return $this->printItems;
    }

    public function addPrintItem(PrintItem $printItem): static
    {
        if (!$this->printItems->contains($printItem)) {
            $this->printItems->add($printItem);
            $printItem->setPrinter($this);
        }

        return $this;
    }

    public function removePrintItem(PrintItem $printItem): static
    {
        if ($this->printItems->removeElement($printItem)) {
            // set the owning side to null (unless already changed)
            if ($printItem->getPrinter() === $this) {
                $printItem->setPrinter(null);
            }
        }

        return $this;
    }
}
