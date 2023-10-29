<?php

namespace App\Entity;

use App\Repository\StoreroomTicketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StoreroomTicketRepository::class)]
class StoreroomTicket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'storeroomTickets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Business $bid = null;

    #[ORM\ManyToOne(inversedBy: 'storeroomTickets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $submitter = null;

    #[ORM\Column(length: 255)]
    private ?string $date = null;

    #[ORM\Column(length: 255)]
    private ?string $dateSubmit = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $transfer = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $receiver = null;

    #[ORM\Column(length: 255)]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'storeroomTickets')]
    private ?Person $Person = null;

    #[ORM\ManyToOne(inversedBy: 'storeroomTickets')]
    private ?HesabdariDoc $doc = null;

    #[ORM\ManyToOne(inversedBy: 'storeroomTickets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Year $year = null;

    #[ORM\OneToMany(mappedBy: 'ticket', targetEntity: StoreroomItem::class, orphanRemoval: true)]
    private Collection $storeroomItems;

    #[ORM\ManyToOne(inversedBy: 'storeroomTickets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Storeroom $storeroom = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?StoreroomTransferType $transferType = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $referral = null;

    #[ORM\Column(length: 255)]
    private ?string $typeString = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $des = null;

    public function __construct()
    {
        $this->storeroomItems = new ArrayCollection();
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

    public function getSubmitter(): ?User
    {
        return $this->submitter;
    }

    public function setSubmitter(?User $submitter): static
    {
        $this->submitter = $submitter;

        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(string $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getDateSubmit(): ?string
    {
        return $this->dateSubmit;
    }

    public function setDateSubmit(string $dateSubmit): static
    {
        $this->dateSubmit = $dateSubmit;

        return $this;
    }

    public function getTransfer(): ?string
    {
        return $this->transfer;
    }

    public function setTransfer(?string $transfer): static
    {
        $this->transfer = $transfer;

        return $this;
    }

    public function getReceiver(): ?string
    {
        return $this->receiver;
    }

    public function setReceiver(?string $receiver): static
    {
        $this->receiver = $receiver;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getPerson(): ?Person
    {
        return $this->Person;
    }

    public function setPerson(?Person $Person): static
    {
        $this->Person = $Person;

        return $this;
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

    public function getYear(): ?Year
    {
        return $this->year;
    }

    public function setYear(?Year $year): static
    {
        $this->year = $year;

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
            $storeroomItem->setTicket($this);
        }

        return $this;
    }

    public function removeStoreroomItem(StoreroomItem $storeroomItem): static
    {
        if ($this->storeroomItems->removeElement($storeroomItem)) {
            // set the owning side to null (unless already changed)
            if ($storeroomItem->getTicket() === $this) {
                $storeroomItem->setTicket(null);
            }
        }

        return $this;
    }

    public function getStoreroom(): ?Storeroom
    {
        return $this->storeroom;
    }

    public function setStoreroom(?Storeroom $storeroom): static
    {
        $this->storeroom = $storeroom;

        return $this;
    }

    public function getTransferType(): ?StoreroomTransferType
    {
        return $this->transferType;
    }

    public function setTransferType(?StoreroomTransferType $transferType): static
    {
        $this->transferType = $transferType;

        return $this;
    }

    public function getReferral(): ?string
    {
        return $this->referral;
    }

    public function setReferral(?string $referral): static
    {
        $this->referral = $referral;

        return $this;
    }

    public function getTypeString(): ?string
    {
        return $this->typeString;
    }

    public function setTypeString(string $typeString): static
    {
        $this->typeString = $typeString;

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
}
