<?php

namespace App\Entity;

use App\Repository\RegistryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RegistryRepository::class)]
class Registry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $root = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $valueOfKey = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoot(): ?string
    {
        return $this->root;
    }

    public function setRoot(?string $root): static
    {
        $this->root = $root;

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

    public function getValueOfKey(): ?string
    {
        return $this->valueOfKey;
    }

    public function setValueOfKey(string $valueOfKey): static
    {
        $this->valueOfKey = $valueOfKey;

        return $this;
    }
}
