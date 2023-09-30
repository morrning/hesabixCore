<?php

namespace App\Entity;

use App\Repository\CommodityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\Entity(repositoryClass: CommodityRepository::class)]
class Commodity
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\ManyToOne(targetEntity: CommodityUnit::class, inversedBy: 'commodities')]
    #[ORM\JoinColumn(nullable: false)]
    private $unit;

    #[ORM\ManyToOne(targetEntity: Business::class, inversedBy: 'commodities')]
    #[ORM\JoinColumn(nullable: false)]
    #[Ignore]
    private $bid;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $des;

    #[ORM\Column(type: 'bigint')]
    private $code;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $priceBuy;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $priceSell;

    #[ORM\OneToMany(mappedBy: 'commodity', targetEntity: HesabdariRow::class)]
    #[Ignore]
    private Collection $hesabdariRows;

    public function __construct()
    {
        $this->setPriceBuy(0);
        $this->setPriceSell(0);
        $this->hesabdariRows = new ArrayCollection();
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

    public function getUnit(): CommodityUnit | string|null
    {
        return $this->unit;
    }

    public function setUnit(CommodityUnit | string|null $unit): self
    {
        $this->unit = $unit;

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

    public function getDes(): ?string
    {
        return $this->des;
    }

    public function setDes(?string $des): self
    {
        $this->des = $des;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getPriceBuy(): ?int
    {
        return $this->priceBuy;
    }

    public function setPriceBuy(?int $priceBuy): self
    {
        $this->priceBuy = $priceBuy;

        return $this;
    }

    public function getPriceSell(): ?int
    {
        return $this->priceSell;
    }

    public function setPriceSell(?int $priceSell): self
    {
        $this->priceSell = $priceSell;

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
            $hesabdariRow->setCommodity($this);
        }

        return $this;
    }

    public function removeHesabdariRow(HesabdariRow $hesabdariRow): self
    {
        if ($this->hesabdariRows->removeElement($hesabdariRow)) {
            // set the owning side to null (unless already changed)
            if ($hesabdariRow->getCommodity() === $this) {
                $hesabdariRow->setCommodity(null);
            }
        }

        return $this;
    }
}
