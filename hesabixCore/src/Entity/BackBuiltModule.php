<?php

namespace App\Entity;

use App\Repository\BackBuiltModuleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BackBuiltModuleRepository::class)]
class BackBuiltModule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'backBuiltModules')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $submitter = null;

    #[ORM\Column(length: 40)]
    private ?string $dateSubmit = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $code = null;

    #[ORM\Column(nullable: true)]
    private ?bool $locked = null;

    #[ORM\Column(nullable: true)]
    private ?bool $public = null;

    #[ORM\Column(length: 120)]
    private ?string $type = null;

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

    public function getDateSubmit(): ?string
    {
        return $this->dateSubmit;
    }

    public function setDateSubmit(string $dateSubmit): static
    {
        $this->dateSubmit = $dateSubmit;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function isLocked(): ?bool
    {
        return $this->locked;
    }

    public function setLocked(?bool $locked): static
    {
        $this->locked = $locked;

        return $this;
    }

    public function isPublic(): ?bool
    {
        return $this->public;
    }

    public function setPublic(?bool $public): static
    {
        $this->public = $public;

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
