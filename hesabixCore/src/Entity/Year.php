<?php

namespace App\Entity;

use App\Repository\YearRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\Entity(repositoryClass: YearRepository::class)]
class Year
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\ManyToOne(inversedBy: 'years')]
    #[ORM\JoinColumn(nullable: false)]
    #[Ignore]
    private ?Business $bid = null;

    #[ORM\Column(nullable: true)]
    private ?bool $head = null;

    #[ORM\OneToMany(mappedBy: 'year', targetEntity: HesabdariDoc::class, orphanRemoval: true)]
    #[Ignore]
    private Collection $hesabdariDocs;

    #[ORM\Column(length: 255)]
    private ?string $start = null;

    #[ORM\Column(length: 255)]
    private ?string $end = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $now = null;

    #[ORM\OneToMany(mappedBy: 'year', targetEntity: HesabdariRow::class, orphanRemoval: true)]
    #[Ignore]
    private Collection $hesabdariRows;

    public function __construct()
    {
        $this->hesabdariDocs = new ArrayCollection();
        $this->hesabdariRows = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

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

    public function isHead(): ?bool
    {
        return $this->head;
    }

    public function setHead(?bool $head): self
    {
        $this->head = $head;

        return $this;
    }

    /**
     * @return Collection<int, HesabdariDoc>
     */
    public function getHesabdariDocs(): Collection
    {
        return $this->hesabdariDocs;
    }

    public function addHesabdariDoc(HesabdariDoc $hesabdariDoc): self
    {
        if (!$this->hesabdariDocs->contains($hesabdariDoc)) {
            $this->hesabdariDocs->add($hesabdariDoc);
            $hesabdariDoc->setYear($this);
        }

        return $this;
    }

    public function removeHesabdariDoc(HesabdariDoc $hesabdariDoc): self
    {
        if ($this->hesabdariDocs->removeElement($hesabdariDoc)) {
            // set the owning side to null (unless already changed)
            if ($hesabdariDoc->getYear() === $this) {
                $hesabdariDoc->setYear(null);
            }
        }

        return $this;
    }

    public function getStart(): ?string
    {
        return $this->start;
    }

    public function setStart(string $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?string
    {
        return $this->end;
    }

    public function setEnd(string $end): self
    {
        $this->end = $end;

        return $this;
    }

    public function getNow(): ?string
    {
        return $this->now;
    }

    public function setNow(?string $now): self
    {
        $this->now = $now;

        return $this;
    }

    /**
     * @return Collection<int, HesabdariRow>
     */
    public function getHesabdariRows(): Collection
    {
        return $this->hesabdariRows;
    }

    public function addHesabdariRow(HesabdariRow $hesabdariRow): self
    {
        if (!$this->hesabdariRows->contains($hesabdariRow)) {
            $this->hesabdariRows->add($hesabdariRow);
            $hesabdariRow->setYear($this);
        }

        return $this;
    }

    public function removeHesabdariRow(HesabdariRow $hesabdariRow): self
    {
        if ($this->hesabdariRows->removeElement($hesabdariRow)) {
            // set the owning side to null (unless already changed)
            if ($hesabdariRow->getYear() === $this) {
                $hesabdariRow->setYear(null);
            }
        }

        return $this;
    }
}
