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

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shortlink = null;

    #[ORM\ManyToOne]
    #[Ignore]
    private ?WalletTransaction $walletTransaction = null;

    #[ORM\ManyToMany(targetEntity: self::class)]
    #[Ignore]
    private Collection $relatedDocs;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $status = null;

    #[ORM\OneToMany(mappedBy: 'doc', targetEntity: StoreroomTicket::class)]
    #[Ignore]
    private Collection $storeroomTickets;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $tempStatus = null;

    #[ORM\OneToMany(mappedBy: 'doc', targetEntity: Log::class)]
    private Collection $logs;

    #[ORM\ManyToOne(inversedBy: 'hesabdariDocs')]
    private ?InvoiceType $InvoiceLabel = null;

    #[ORM\OneToMany(mappedBy: 'doc', targetEntity: Note::class)]
    private Collection $notes;

    public function __construct()
    {
        $this->hesabdariRows = new ArrayCollection();
        $this->plugNoghreOrders = new ArrayCollection();
        $this->relatedDocs = new ArrayCollection();
        $this->storeroomTickets = new ArrayCollection();
        $this->logs = new ArrayCollection();
        $this->notes = new ArrayCollection();
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

    public function getShortlink(): ?string
    {
        return $this->shortlink;
    }

    public function setShortlink(?string $shortlink): static
    {
        $this->shortlink = $shortlink;

        return $this;
    }

    public function getWalletTransaction(): ?WalletTransaction
    {
        return $this->walletTransaction;
    }

    public function setWalletTransaction(?WalletTransaction $walletTransaction): static
    {
        $this->walletTransaction = $walletTransaction;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getRelatedDocs(): Collection
    {
        return $this->relatedDocs;
    }

    public function addRelatedDoc(self $relatedDoc): static
    {
        if (!$this->relatedDocs->contains($relatedDoc)) {
            $this->relatedDocs->add($relatedDoc);
        }

        return $this;
    }

    public function removeRelatedDoc(self $relatedDoc): static
    {
        $this->relatedDocs->removeElement($relatedDoc);

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
            $storeroomTicket->setDoc($this);
        }

        return $this;
    }

    public function removeStoreroomTicket(StoreroomTicket $storeroomTicket): static
    {
        if ($this->storeroomTickets->removeElement($storeroomTicket)) {
            // set the owning side to null (unless already changed)
            if ($storeroomTicket->getDoc() === $this) {
                $storeroomTicket->setDoc(null);
            }
        }

        return $this;
    }

    public function getTempStatus(): ?array
    {
        return $this->tempStatus;
    }

    public function setTempStatus(?array $tempStatus): static
    {
        $this->tempStatus = $tempStatus;

        return $this;
    }

    /**
     * @return Collection<int, Log>
     */
    public function getLogs(): Collection
    {
        return $this->logs;
    }

    public function addLog(Log $log): static
    {
        if (!$this->logs->contains($log)) {
            $this->logs->add($log);
            $log->setDoc($this);
        }

        return $this;
    }

    public function removeLog(Log $log): static
    {
        if ($this->logs->removeElement($log)) {
            // set the owning side to null (unless already changed)
            if ($log->getDoc() === $this) {
                $log->setDoc(null);
            }
        }

        return $this;
    }

    public function getInvoiceLabel(): ?InvoiceType
    {
        return $this->InvoiceLabel;
    }

    public function setInvoiceLabel(?InvoiceType $InvoiceLabel): static
    {
        $this->InvoiceLabel = $InvoiceLabel;

        return $this;
    }

    /**
     * @return Collection<int, Note>
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(Note $note): static
    {
        if (!$this->notes->contains($note)) {
            $this->notes->add($note);
            $note->setDoc($this);
        }

        return $this;
    }

    public function removeNote(Note $note): static
    {
        if ($this->notes->removeElement($note)) {
            // set the owning side to null (unless already changed)
            if ($note->getDoc() === $this) {
                $note->setDoc(null);
            }
        }

        return $this;
    }
}
