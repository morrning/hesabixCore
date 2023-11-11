<?php

namespace App\Entity;

use App\Repository\ArchiveFileRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArchiveFileRepository::class)]
class ArchiveFile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'archiveFiles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Business $bid = null;

    #[ORM\Column(length: 255)]
    private ?string $dateSubmit = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dateMod = null;

    #[ORM\ManyToOne(inversedBy: 'archiveFiles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $Submitter = null;

    #[ORM\Column(length: 255)]
    private ?string $filename = null;

    #[ORM\Column(length: 255)]
    private ?string $cat = null;

    #[ORM\Column(length: 255)]
    private ?string $fileType = null;

    #[ORM\Column(nullable: true)]
    private ?bool $public = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $des = null;

    #[ORM\Column(length: 255)]
    private ?string $relatedDocType = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $relatedDocCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fileSize = null;

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

    public function getDateSubmit(): ?string
    {
        return $this->dateSubmit;
    }

    public function setDateSubmit(string $dateSubmit): static
    {
        $this->dateSubmit = $dateSubmit;

        return $this;
    }

    public function getDateMod(): ?string
    {
        return $this->dateMod;
    }

    public function setDateMod(?string $dateMod): static
    {
        $this->dateMod = $dateMod;

        return $this;
    }

    public function getSubmitter(): ?User
    {
        return $this->Submitter;
    }

    public function setSubmitter(?User $Submitter): static
    {
        $this->Submitter = $Submitter;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getCat(): ?string
    {
        return $this->cat;
    }

    public function setCat(string $cat): static
    {
        $this->cat = $cat;

        return $this;
    }

    public function getFileType(): ?string
    {
        return $this->fileType;
    }

    public function setFileType(string $fileType): static
    {
        $this->fileType = $fileType;

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

    public function getDes(): ?string
    {
        return $this->des;
    }

    public function setDes(?string $des): static
    {
        $this->des = $des;

        return $this;
    }

    public function getRelatedDocType(): ?string
    {
        return $this->relatedDocType;
    }

    public function setRelatedDocType(string $relatedDocType): static
    {
        $this->relatedDocType = $relatedDocType;

        return $this;
    }

    public function getRelatedDocCode(): ?string
    {
        return $this->relatedDocCode;
    }

    public function setRelatedDocCode(?string $relatedDocCode): static
    {
        $this->relatedDocCode = $relatedDocCode;

        return $this;
    }

    public function getFileSize(): ?string
    {
        return $this->fileSize;
    }

    public function setFileSize(?string $fileSize): static
    {
        $this->fileSize = $fileSize;

        return $this;
    }
}
