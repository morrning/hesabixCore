<?php

namespace App\Entity;

use App\Repository\PlugRepserviceOrderStateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlugRepserviceOrderStateRepository::class)]
class PlugRepserviceOrderState
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\Column(length: 255)]
    private ?string $code = null;

    #[ORM\OneToMany(mappedBy: 'state', targetEntity: PlugRepserviceOrder::class, orphanRemoval: true)]
    private Collection $plugRepserviceOrders;

    public function __construct()
    {
        $this->plugRepserviceOrders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return Collection<int, PlugRepserviceOrder>
     */
    public function getPlugRepserviceOrders(): Collection
    {
        return $this->plugRepserviceOrders;
    }

    public function addPlugRepserviceOrder(PlugRepserviceOrder $plugRepserviceOrder): static
    {
        if (!$this->plugRepserviceOrders->contains($plugRepserviceOrder)) {
            $this->plugRepserviceOrders->add($plugRepserviceOrder);
            $plugRepserviceOrder->setState($this);
        }

        return $this;
    }

    public function removePlugRepserviceOrder(PlugRepserviceOrder $plugRepserviceOrder): static
    {
        if ($this->plugRepserviceOrders->removeElement($plugRepserviceOrder)) {
            // set the owning side to null (unless already changed)
            if ($plugRepserviceOrder->getState() === $this) {
                $plugRepserviceOrder->setState(null);
            }
        }

        return $this;
    }
}
