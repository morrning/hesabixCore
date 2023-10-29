<?php

namespace App\Entity;

use App\Repository\StoreroomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StoreroomRepository::class)]
class Storeroom
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'storerooms')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Business $bid = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $manager = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adr = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tel = null;

    #[ORM\Column(nullable: true)]
    private ?bool $active = null;

    #[ORM\OneToMany(mappedBy: 'Storeroom', targetEntity: StoreroomItem::class, orphanRemoval: true)]
    private Collection $storeroomItems;

    #[ORM\OneToMany(mappedBy: 'storeroom', targetEntity: StoreroomTicket::class, orphanRemoval: true)]
    private Collection $storeroomTickets;

    public function __construct()
    {
        $this->storeroomItems = new ArrayCollection();
        $this->storeroomTickets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getBid(): ?Business
    {
        return $this->bid;
    }

    public function setBid(?Business $bid): static
    {
        $this->bid = $bid;

        return $this;
    }

    public function getManager(): ?string
    {
        return $this->manager;
    }

    public function setManager(?string $manager): static
    {
        $this->manager = $manager;

        return $this;
    }

    public function getAdr(): ?string
    {
        return $this->adr;
    }

    public function setAdr(?string $adr): static
    {
        $this->adr = $adr;

        return $this;
    }

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(?string $tel): static
    {
        $this->tel = $tel;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Collection<int, StoreroomItem>
     */
    public function getStoreroomItems(): Collection
    {
        return $this->storeroomItems;
    }

    public function addStoreroomItem(StoreroomItem $storeroomItem): static
    {
        if (!$this->storeroomItems->contains($storeroomItem)) {
            $this->storeroomItems->add($storeroomItem);
            $storeroomItem->setStoreroom($this);
        }

        return $this;
    }

    public function removeStoreroomItem(StoreroomItem $storeroomItem): static
    {
        if ($this->storeroomItems->removeElement($storeroomItem)) {
            // set the owning side to null (unless already changed)
            if ($storeroomItem->getStoreroom() === $this) {
                $storeroomItem->setStoreroom(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, StoreroomTicket>
     */
    public function getStoreroomTickets(): Collection
    {
        return $this->storeroomTickets;
    }

    public function addStoreroomTicket(StoreroomTicket $storeroomTicket): static
    {
        if (!$this->storeroomTickets->contains($storeroomTicket)) {
            $this->storeroomTickets->add($storeroomTicket);
            $storeroomTicket->setStoreroom($this);
        }

        return $this;
    }

    public function removeStoreroomTicket(StoreroomTicket $storeroomTicket): static
    {
        if ($this->storeroomTickets->removeElement($storeroomTicket)) {
            // set the owning side to null (unless already changed)
            if ($storeroomTicket->getStoreroom() === $this) {
                $storeroomTicket->setStoreroom(null);
            }
        }

        return $this;
    }
}
