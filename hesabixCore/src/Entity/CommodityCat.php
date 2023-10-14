<?php

namespace App\Entity;

use App\Repository\CommodityCatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommodityCatRepository::class)]
class CommodityCat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Business $bid = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $upper = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    private ?bool $root = null;

    #[ORM\OneToMany(mappedBy: 'cat', targetEntity: Commodity::class)]
    private Collection $commodities;

    public function __construct()
    {
        $this->commodities = new ArrayCollection();
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

    public function getUpper(): ?string
    {
        return $this->upper;
    }

    public function setUpper(?string $upper): static
    {
        $this->upper = $upper;

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

    public function isRoot(): ?bool
    {
        return $this->root;
    }

    public function setRoot(?bool $root): static
    {
        $this->root = $root;

        return $this;
    }

    /**
     * @return Collection<int, Commodity>
     */
    public function getCommodities(): Collection
    {
        return $this->commodities;
    }

    public function addCommodity(Commodity $commodity): static
    {
        if (!$this->commodities->contains($commodity)) {
            $this->commodities->add($commodity);
            $commodity->setCat($this);
        }

        return $this;
    }

    public function removeCommodity(Commodity $commodity): static
    {
        if ($this->commodities->removeElement($commodity)) {
            // set the owning side to null (unless already changed)
            if ($commodity->getCat() === $this) {
                $commodity->setCat(null);
            }
        }

        return $this;
    }
}
