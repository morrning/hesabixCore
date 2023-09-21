<?php

namespace App\Entity;

use App\Repository\HesabdariDocRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\Entity(repositoryClass: HesabdariDocRepository::class)]
class HesabdariDoc
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'hesabdariDocs')]
    #[Ignore]
    private ?Business $bid = null;

    #[ORM\OneToMany(mappedBy: 'doc', targetEntity: HesabdariRow::class, orphanRemoval: true)]
    #[Ignore]
    private Collection $hesabdariRows;

    #[ORM\ManyToOne(inversedBy: 'hesabdariDocs')]
    #[ORM\JoinColumn(nullable: false)]
    #[Ignore]
    private ?User $submitter = null;

    #[ORM\Column(length: 50)]
    private ?string $dateSubmit = null;

    #[ORM\Column(length: 50)]
    private ?string $date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?string $code = null;

    #[ORM\ManyToOne(inversedBy: 'hesabdariDocs')]
    #[ORM\JoinColumn(nullable: false)]
    #[Ignore]
    private ?Year $year = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $des = null;

    #[ORM\Column(type: 'string', length: 255,nullable: true)]
    private int $amount = 0;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Ignore]
    private ?Money $money = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mdate = null;

    #[ORM\OneToMany(mappedBy: 'doc', targetEntity: PlugNoghreOrder::class, orphanRemoval: true)]
    #[Ignore]
    private Collection $plugNoghreOrders;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $plugin = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $refData = null;

    public function __construct()
    {
        $this->hesabdariRows = new ArrayCollection();
        $this->plugNoghreOrders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
            $hesabdariRow->setDoc($this);
        }

        return $this;
    }

    public function removeHesabdariRow(HesabdariRow $hesabdariRow): self
    {
        if ($this->hesabdariRows->removeElement($hesabdariRow)) {
            // set the owning side to null (unless already changed)
            if ($hesabdariRow->getDoc() === $this) {
                $hesabdariRow->setDoc(null);
            }
        }

        return $this;
    }

    public function getSubmitter(): ?User
    {
        return $this->submitter;
    }

    public function setSubmitter(?User $submitter): self
    {
        $this->submitter = $submitter;

        return $this;
    }

    public function getDateSubmit(): ?string
    {
        return $this->dateSubmit;
    }

    public function setDateSubmit(string $dateSubmit): self
    {
        $this->dateSubmit = $dateSubmit;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

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

    public function getYear(): ?Year
    {
        return $this->year;
    }

    public function setYear(?Year $year): self
    {
        $this->year = $year;

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

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(?int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getMoney(): ?Money
    {
        return $this->money;
    }

    public function setMoney(?Money $money): self
    {
        $this->money = $money;

        return $this;
    }

    public function getMdate(): ?string
    {
        return $this->mdate;
    }

    public function setMdate(?string $mdate): self
    {
        $this->mdate = $mdate;

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
            $plugNoghreOrder->setDoc($this);
        }

        return $this;
    }

    public function removePlugNoghreOrder(PlugNoghreOrder $plugNoghreOrder): static
    {
        if ($this->plugNoghreOrders->removeElement($plugNoghreOrder)) {
            // set the owning side to null (unless already changed)
            if ($plugNoghreOrder->getDoc() === $this) {
                $plugNoghreOrder->setDoc(null);
            }
        }

        return $this;
    }

    public function getPlugin(): ?string
    {
        return $this->plugin;
    }

    public function setPlugin(?string $plugin): static
    {
        $this->plugin = $plugin;

        return $this;
    }

    public function getRefData(): ?string
    {
        return $this->refData;
    }

    public function setRefData(?string $refData): static
    {
        $this->refData = $refData;

        return $this;
    }
}
