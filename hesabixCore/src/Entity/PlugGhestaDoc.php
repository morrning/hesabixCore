<?php

namespace App\Entity;

use App\Repository\PlugGhestaDocRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlugGhestaDocRepository::class)]
class PlugGhestaDoc
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'PlugGhestaDocs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Business $bid = null;

    #[ORM\ManyToOne(inversedBy: 'PlugGhestaDocs')]
    private ?User $submitter = null;

    #[ORM\Column(length: 25)]
    private ?string $dateSubmit = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?string $count = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?string $profitPercent = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $profitAmount = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $profitType = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $daysPay = null;

    #[ORM\ManyToOne(inversedBy: 'PlugGhestaDocs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Person $person = null;

    /**
     * @var Collection<int, PlugGhestaItem>
     */
    #[ORM\OneToMany(targetEntity: PlugGhestaItem::class, mappedBy: 'doc', orphanRemoval: true)]
    private Collection $plugGhestaItems;

    #[ORM\ManyToOne(inversedBy: 'plugGhestaDocs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?HesabdariDoc $mainDoc = null;

    public function __construct()
    {
        $this->plugGhestaItems = new ArrayCollection();
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

    public function getCount(): ?string
    {
        return $this->count;
    }

    public function setCount(string $count): static
    {
        $this->count = $count;

        return $this;
    }

    public function getProfitPercent(): ?string
    {
        return $this->profitPercent;
    }

    public function setProfitPercent(string $profitPercent): static
    {
        $this->profitPercent = $profitPercent;

        return $this;
    }

    public function getProfitAmount(): ?string
    {
        return $this->profitAmount;
    }

    public function setProfitAmount(?string $profitAmount): static
    {
        $this->profitAmount = $profitAmount;

        return $this;
    }

    public function getProfitType(): ?string
    {
        return $this->profitType;
    }

    public function setProfitType(?string $profitType): static
    {
        $this->profitType = $profitType;

        return $this;
    }

    public function getDaysPay(): ?float
    {
        return $this->daysPay;
    }

    public function setDaysPay(?float $daysPay): static
    {
        $this->daysPay = $daysPay;

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

    /**
     * @return Collection<int, PlugGhestaItem>
     */
    public function getPlugGhestaItems(): Collection
    {
        return $this->plugGhestaItems;
    }

    public function addPlugGhestaItem(PlugGhestaItem $plugGhestaItem): static
    {
        if (!$this->plugGhestaItems->contains($plugGhestaItem)) {
            $this->plugGhestaItems->add($plugGhestaItem);
            $plugGhestaItem->setDoc($this);
        }

        return $this;
    }

    public function removePlugGhestaItem(PlugGhestaItem $plugGhestaItem): static
    {
        if ($this->plugGhestaItems->removeElement($plugGhestaItem)) {
            // set the owning side to null (unless already changed)
            if ($plugGhestaItem->getDoc() === $this) {
                $plugGhestaItem->setDoc(null);
            }
        }

        return $this;
    }

    public function getMainDoc(): ?HesabdariDoc
    {
        return $this->mainDoc;
    }

    public function setMainDoc(?HesabdariDoc $mainDoc): static
    {
        $this->mainDoc = $mainDoc;

        return $this;
    }
}
