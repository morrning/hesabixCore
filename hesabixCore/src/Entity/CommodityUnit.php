<?php

namespace App\Entity;

use App\Repository\CommodityUnitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\Entity(repositoryClass: CommodityUnitRepository::class)]
class CommodityUnit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\OneToMany(mappedBy: 'unit', targetEntity: Commodity::class, orphanRemoval: true)]
    #[Ignore]
    private $commodities;

    #[ORM\Column(nullable: true)]
    private ?int $floatNumber = null;

    public function __construct()
    {
        $this->commodities = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Commodity>
     */
    public function getCommodities(): Collection
    {
        return $this->commodities;
    }

    public function addCommodity(Commodity $commodity): self
    {
        if (!$this->commodities->contains($commodity)) {
            $this->commodities[] = $commodity;
            $commodity->setUnit($this);
        }

        return $this;
    }

    public function removeCommodity(Commodity $commodity): self
    {
        if ($this->commodities->removeElement($commodity)) {
            // set the owning side to null (unless already changed)
            if ($commodity->getUnit() === $this) {
                $commodity->setUnit(null);
            }
        }

        return $this;
    }

    public function getFloatNumber(): ?int
    {
        return $this->floatNumber;
    }

    public function setFloatNumber(?int $floatNumber): static
    {
        $this->floatNumber = $floatNumber;

        return $this;
    }
}
