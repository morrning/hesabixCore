<?php

namespace App\Entity;

use App\Repository\PersonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: PersonRepository::class)]
class Person
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    private ?string $nikename = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 12, nullable: true)]
    private ?string $tel = null;

    #[ORM\Column(length: 12, nullable: true)]
    private ?string $mobile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $des = null;

    #[ORM\ManyToOne(inversedBy: 'people')]
    #[ORM\JoinColumn(nullable: false)]
    #[MaxDepth(1)]
    #[Ignore]
    private ?Business $bid = null;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: HesabdariRow::class)]
    #[Ignore]
    private Collection $hesabdariRows;

    #[ORM\Column(nullable: true)]
    private ?bool $plugNoghreMorsa = null;

    #[ORM\Column(nullable: true)]
    private ?bool $plugNoghreHakak = null;

    #[ORM\Column(nullable: true)]
    private ?bool $plugNoghreTarash = null;

    #[ORM\Column(nullable: true)]
    private ?bool $employe = null;

    #[ORM\Column(nullable: true)]
    private ?bool $plugNoghreGhalam = null;

    #[ORM\OneToMany(mappedBy: 'morsa', targetEntity: PlugNoghreOrder::class)]
    private Collection $plugNoghreOrders;

    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: PlugNoghreOrder::class)]
    private Collection $ordersFromCustomer;

    public function __construct()
    {
        $this->hesabdariRows = new ArrayCollection();
        $this->plugNoghreOrders = new ArrayCollection();
        $this->ordersFromCustomer = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getNikename(): ?string
    {
        return $this->nikename;
    }

    public function setNikename(string $nikename): self
    {
        $this->nikename = $nikename;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(?string $tel): self
    {
        $this->tel = $tel;

        return $this;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(?string $mobile): self
    {
        $this->mobile = $mobile;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

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

    public function getBid(): ?Business
    {
        return $this->bid;
    }

    public function setBid(?Business $bid): self
    {
        $this->bid = $bid;

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
            $hesabdariRow->setPerson($this);
        }

        return $this;
    }

    public function removeHesabdariRow(HesabdariRow $hesabdariRow): self
    {
        if ($this->hesabdariRows->removeElement($hesabdariRow)) {
            // set the owning side to null (unless already changed)
            if ($hesabdariRow->getPerson() === $this) {
                $hesabdariRow->setPerson(null);
            }
        }

        return $this;
    }

    public function isPlugNoghreMorsa(): ?bool
    {
        return $this->plugNoghreMorsa;
    }

    public function setPlugNoghreMorsa(bool $plugNoghreMorsa): static
    {
        $this->plugNoghreMorsa = $plugNoghreMorsa;

        return $this;
    }

    public function isPlugNoghreHakak(): ?bool
    {
        return $this->plugNoghreHakak;
    }

    public function setPlugNoghreHakak(?bool $plugNoghreHakak): static
    {
        $this->plugNoghreHakak = $plugNoghreHakak;

        return $this;
    }

    public function isPlugNoghreTarash(): ?bool
    {
        return $this->plugNoghreTarash;
    }

    public function setPlugNoghreTarash(bool $plugNoghreTarash): static
    {
        $this->plugNoghreTarash = $plugNoghreTarash;

        return $this;
    }

    public function isEmploye(): ?bool
    {
        return $this->employe;
    }

    public function setEmploye(bool $employe): static
    {
        $this->employe = $employe;

        return $this;
    }

    public function isPlugNoghreGhalam(): ?bool
    {
        return $this->plugNoghreGhalam;
    }

    public function setPlugNoghreGhalam(?bool $plugNoghreGhalam): static
    {
        $this->plugNoghreGhalam = $plugNoghreGhalam;

        return $this;
    }

    /**
     * @return Collection<int, PlugNoghreOrder>
     */
    public function getPlugNoghreOrders(): Collection
    {
        return $this->plugNoghreOrders;
    }

    public function addPlugNoghreOrder(PlugNoghreOrder $plugNoghreOrder): static
    {
        if (!$this->plugNoghreOrders->contains($plugNoghreOrder)) {
            $this->plugNoghreOrders->add($plugNoghreOrder);
            $plugNoghreOrder->setMorsa($this);
        }

        return $this;
    }

    public function removePlugNoghreOrder(PlugNoghreOrder $plugNoghreOrder): static
    {
        if ($this->plugNoghreOrders->removeElement($plugNoghreOrder)) {
            // set the owning side to null (unless already changed)
            if ($plugNoghreOrder->getMorsa() === $this) {
                $plugNoghreOrder->setMorsa(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PlugNoghreOrder>
     */
    public function getOrdersFromCustomer(): Collection
    {
        return $this->ordersFromCustomer;
    }

    public function addOrdersFromCustomer(PlugNoghreOrder $ordersFromCustomer): static
    {
        if (!$this->ordersFromCustomer->contains($ordersFromCustomer)) {
            $this->ordersFromCustomer->add($ordersFromCustomer);
            $ordersFromCustomer->setCustomer($this);
        }

        return $this;
    }

    public function removeOrdersFromCustomer(PlugNoghreOrder $ordersFromCustomer): static
    {
        if ($this->ordersFromCustomer->removeElement($ordersFromCustomer)) {
            // set the owning side to null (unless already changed)
            if ($ordersFromCustomer->getCustomer() === $this) {
                $ordersFromCustomer->setCustomer(null);
            }
        }

        return $this;
    }
}
