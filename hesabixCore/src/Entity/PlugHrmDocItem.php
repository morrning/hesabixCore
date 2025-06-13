<?php

namespace App\Entity;

use App\Repository\PlugHrmDocItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlugHrmDocItemRepository::class)]
class PlugHrmDocItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PlugHrmDoc $doc = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Person $person = null;

    #[ORM\Column]
    private ?int $baseSalary = 0;

    #[ORM\Column]
    private ?int $overtime = 0;

    #[ORM\Column]
    private ?int $shift = 0;

    #[ORM\Column]
    private ?int $night = 0;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDoc(): ?PlugHrmDoc
    {
        return $this->doc;
    }

    public function setDoc(?PlugHrmDoc $doc): static
    {
        $this->doc = $doc;
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

    public function getBaseSalary(): ?int
    {
        return $this->baseSalary;
    }

    public function setBaseSalary(int $baseSalary): static
    {
        $this->baseSalary = $baseSalary;
        return $this;
    }

    public function getOvertime(): ?int
    {
        return $this->overtime;
    }

    public function setOvertime(int $overtime): static
    {
        $this->overtime = $overtime;
        return $this;
    }

    public function getShift(): ?int
    {
        return $this->shift;
    }

    public function setShift(int $shift): static
    {
        $this->shift = $shift;
        return $this;
    }

    public function getNight(): ?int
    {
        return $this->night;
    }

    public function setNight(int $night): static
    {
        $this->night = $night;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getTotal(): int
    {
        return $this->baseSalary + $this->overtime + $this->shift + $this->night;
    }
} 