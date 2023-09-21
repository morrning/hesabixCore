<?php

namespace App\Entity;

use App\Repository\PlugNoghreOrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\Entity(repositoryClass: PlugNoghreOrderRepository::class)]
class PlugNoghreOrder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'plugNoghreOrders')]
    #[ORM\JoinColumn(nullable: false)]
    #[Ignore]
    private ?HesabdariDoc $doc = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $deliveryDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $place = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'plugNoghreOrders')]
    #[ORM\JoinColumn(nullable: false)]
    #[Ignore]
    private ?Business $bid = null;

    #[ORM\ManyToOne(inversedBy: 'plugNoghreOrders')]
    #[Ignore]
    private ?Person $morsa = null;

    #[ORM\ManyToOne(inversedBy: 'plugNoghreOrders')]
    #[Ignore]
    private ?Person $tarash = null;

    #[ORM\ManyToOne(inversedBy: 'plugNoghreOrders')]
    #[Ignore]
    private ?Person $hakak = null;

    #[ORM\ManyToOne(inversedBy: 'plugNoghreOrders')]
    #[Ignore]
    private ?Person $ghalam = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $negin = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $noghreAmount = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $neginFee = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ringModel = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ringSize = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $des = null;

    #[ORM\ManyToOne(inversedBy: 'ordersFromCustomer')]
    #[Ignore]
    private ?Person $customer = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $noghreFee = null;


    public function __construct()
    {

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDoc(): ?HesabdariDoc
    {
        return $this->doc;
    }

    public function setDoc(?HesabdariDoc $doc): static
    {
        $this->doc = $doc;

        return $this;
    }

    public function getDeliveryDate(): ?string
    {
        return $this->deliveryDate;
    }

    public function setDeliveryDate(?string $deliveryDate): static
    {
        $this->deliveryDate = $deliveryDate;

        return $this;
    }

    public function getPlace(): ?string
    {
        return $this->place;
    }

    public function setPlace(?string $place): static
    {
        $this->place = $place;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

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

    public function getMorsa(): ?Person
    {
        return $this->morsa;
    }

    public function setMorsa(?Person $morsa): static
    {
        $this->morsa = $morsa;

        return $this;
    }

    public function getTarash(): ?Person
    {
        return $this->tarash;
    }

    public function setTarash(?Person $tarash): static
    {
        $this->tarash = $tarash;

        return $this;
    }

    public function getHakak(): ?Person
    {
        return $this->hakak;
    }

    public function setHakak(?Person $hakak): static
    {
        $this->hakak = $hakak;

        return $this;
    }

    public function getGhalam(): ?Person
    {
        return $this->ghalam;
    }

    public function setGhalam(?Person $ghalam): static
    {
        $this->ghalam = $ghalam;

        return $this;
    }

    public function getNegin(): ?string
    {
        return $this->negin;
    }

    public function setNegin(?string $negin): static
    {
        $this->negin = $negin;

        return $this;
    }

    public function getNoghreAmount(): ?string
    {
        return $this->noghreAmount;
    }

    public function setNoghreAmount(?string $noghreAmount): static
    {
        $this->noghreAmount = $noghreAmount;

        return $this;
    }

    public function getNeginFee(): ?string
    {
        return $this->neginFee;
    }

    public function setNeginFee(?string $neginFee): static
    {
        $this->neginFee = $neginFee;

        return $this;
    }

    public function getRingModel(): ?string
    {
        return $this->ringModel;
    }

    public function setRingModel(?string $ringModel): static
    {
        $this->ringModel = $ringModel;

        return $this;
    }

    public function getRingSize(): ?string
    {
        return $this->ringSize;
    }

    public function setRingSize(?string $ringSize): static
    {
        $this->ringSize = $ringSize;

        return $this;
    }

    public function getDes(): ?string
    {
        return $this->des;
    }

    public function setDes(?string $des): static
    {
        $this->des = $des;

        return $this;
    }

    public function getCustomer(): ?Person
    {
        return $this->customer;
    }

    public function setCustomer(?Person $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    public function getNoghreFee(): ?string
    {
        return $this->noghreFee;
    }

    public function setNoghreFee(?string $noghreFee): static
    {
        $this->noghreFee = $noghreFee;

        return $this;
    }

}
