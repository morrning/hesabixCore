<?php

namespace App\Entity;

use App\Repository\LogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LogRepository::class)]
class Log
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'logs')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'logs')]
    private ?Business $bid = null;

    #[ORM\Column(length: 255)]
    private ?string $dateSubmit = null;

    #[ORM\Column(length: 255)]
    private ?string $part = null;

    #[ORM\Column(length: 255)]
    private ?string $des = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ipaddress = null;

    #[ORM\ManyToOne(inversedBy: 'logs')]
    private ?HesabdariDoc $doc = null;

    #[ORM\ManyToOne(inversedBy: 'logs')]
    private ?PlugRepserviceOrder $repserviceOrder = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getBid(): ?Business
    {
        return $this->bid;
    }

    public function setBid(?Business $bid): self
    {
        $this->bid = $bid;

        return $this;
    }

    public function getDateSubmit(): ?string
    {
        return $this->dateSubmit;
    }

    public function setDateSubmit(string $dateSubmit): self
    {
        $this->dateSubmit = $dateSubmit;

        return $this;
    }

    public function getPart(): ?string
    {
        return $this->part;
    }

    public function setPart(string $part): self
    {
        $this->part = $part;

        return $this;
    }

    public function getDes(): ?string
    {
        return $this->des;
    }

    public function setDes(string $des): self
    {
        $this->des = $des;

        return $this;
    }

    public function getIpaddress(): ?string
    {
        return $this->ipaddress;
    }

    public function setIpaddress(?string $ipaddress): static
    {
        $this->ipaddress = $ipaddress;

        return $this;
    }

    public function getDoc(): ?HesabdariDoc
    {
        return $this->doc;
    }

    public function setDoc(?HesabdariDoc $doc): static
    {
        $this->doc = $doc;

        return $this;
    }

    public function getRepserviceOrder(): ?PlugRepserviceOrder
    {
        return $this->repserviceOrder;
    }

    public function setRepserviceOrder(?PlugRepserviceOrder $repserviceOrder): static
    {
        $this->repserviceOrder = $repserviceOrder;

        return $this;
    }
}
