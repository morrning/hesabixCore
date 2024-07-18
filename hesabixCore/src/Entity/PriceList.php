<?php

namespace App\Entity;

use App\Repository\PriceListRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PriceListRepository::class)]
class PriceList
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'priceLists')]
    #[ORM\JoinColumn(nullable: false)]
    private ?business $bid = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\OneToMany(mappedBy: 'list', targetEntity: PriceListDetail::class, orphanRemoval: true)]
    private Collection $priceListDetails;

    public function __construct()
    {
        $this->priceListDetails = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBid(): ?business
    {
        return $this->bid;
    }

    public function setBid(?business $bid): static
    {
        $this->bid = $bid;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return Collection<int, PriceListDetail>
     */
    public function getPriceListDetails(): Collection
    {
        return $this->priceListDetails;
    }

    public function addPriceListDetail(PriceListDetail $priceListDetail): static
    {
        if (!$this->priceListDetails->contains($priceListDetail)) {
            $this->priceListDetails->add($priceListDetail);
            $priceListDetail->setList($this);
        }

        return $this;
    }

    public function removePriceListDetail(PriceListDetail $priceListDetail): static
    {
        if ($this->priceListDetails->removeElement($priceListDetail)) {
            // set the owning side to null (unless already changed)
            if ($priceListDetail->getList() === $this) {
                $priceListDetail->setList(null);
            }
        }

        return $this;
    }
}
