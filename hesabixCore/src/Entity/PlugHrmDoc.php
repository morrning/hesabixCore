<?php

namespace App\Entity;

use App\Repository\PlugHrmDocRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlugHrmDocRepository::class)]
class PlugHrmDoc
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 10)]
    private ?string $date = null;

    #[ORM\ManyToOne(inversedBy: 'plugHrmDocs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Business $business = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $creator = null;

    #[ORM\Column]
    private ?int $createDate = null;

    #[ORM\OneToMany(mappedBy: 'doc', targetEntity: PlugHrmDocItem::class, orphanRemoval: true)]
    private Collection $items;

    #[ORM\ManyToOne(inversedBy: 'plugHrmDocs')]
    private ?HesabdariDoc $hesabdariDoc = null;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(string $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getBusiness(): ?Business
    {
        return $this->business;
    }

    public function setBusiness(?Business $business): static
    {
        $this->business = $business;
        return $this;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): static
    {
        $this->creator = $creator;
        return $this;
    }

    public function getCreateDate(): ?int
    {
        return $this->createDate;
    }

    public function setCreateDate(int $createDate): static
    {
        $this->createDate = $createDate;
        return $this;
    }

    /**
     * @return Collection<int, PlugHrmDocItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(PlugHrmDocItem $item): static
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setDoc($this);
        }
        return $this;
    }

    public function removeItem(PlugHrmDocItem $item): static
    {
        if ($this->items->removeElement($item)) {
            if ($item->getDoc() === $this) {
                $item->setDoc(null);
            }
        }
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