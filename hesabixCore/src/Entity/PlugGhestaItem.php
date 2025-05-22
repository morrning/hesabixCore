<?php

namespace App\Entity;

use App\Repository\PlugGhestaItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlugGhestaItemRepository::class)]
class PlugGhestaItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'plugGhestaItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PlugGhestaDoc $doc = null;

    #[ORM\Column(length: 25)]
    private ?string $date = null;

    #[ORM\Column(length: 120)]
    private ?string $amount = null;

    #[ORM\Column]
    private ?int $num = null;

    #[ORM\ManyToOne(inversedBy: 'plugGhestaItems')]
    private ?HesabdariDoc $hesabdariDoc = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDoc(): ?PlugGhestaDoc
    {
        return $this->doc;
    }

    public function setDoc(?PlugGhestaDoc $doc): static
    {
        $this->doc = $doc;

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

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getNum(): ?int
    {
        return $this->num;
    }

    public function setNum(int $num): static
    {
        $this->num = $num;

        return $this;
    }

    public function getHesabdariDoc(): ?HesabdariDoc
    {
        return $this->hesabdariDoc;
    }

    public function setHesabdariDoc(?HesabdariDoc $hesabdariDoc): static
    {
        $this->hesabdariDoc = $hesabdariDoc;

        return $this;
    }
}
