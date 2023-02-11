<?php

namespace App\Entity;

use App\Repository\StackContentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StackContentRepository::class)]
class StackContent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'stackContents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $submitter = null;

    #[ORM\Column(length: 50)]
    private ?string $dateSubmit = null;

    #[ORM\ManyToOne(inversedBy: 'stackContents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?StackCat $cat = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $body = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?string $views = null;

    #[ORM\Column(length: 255)]
    private ?string $url = null;

    #[ORM\ManyToOne(targetEntity: self::class)]
    private ?self $upper = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubmitter(): ?User
    {
        return $this->submitter;
    }

    public function setSubmitter(?User $submitter): self
    {
        $this->submitter = $submitter;

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

    public function getCat(): ?StackCat
    {
        return $this->cat;
    }

    public function setCat(?StackCat $cat): self
    {
        $this->cat = $cat;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getViews(): ?string
    {
        return $this->views;
    }

    public function setViews(string $views): self
    {
        $this->views = $views;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getUpper(): ?self
    {
        return $this->upper;
    }

    public function setUpper(?self $upper): self
    {
        $this->upper = $upper;

        return $this;
    }
}
