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

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $company = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shenasemeli = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $codeeghtesadi = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sabt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $keshvar = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ostan = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shahr = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $postalcode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $website = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fax = null;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'Person', targetEntity: StoreroomTicket::class)]
    private Collection $storeroomTickets;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $birthday = null;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: Shareholder::class, orphanRemoval: true)]
    private Collection $shareholders;

    #[ORM\Column(nullable: true)]
    private ?bool $speedAccess = null;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: Cheque::class)]
    private Collection $cheques;

    #[ORM\ManyToMany(targetEntity: PersonType::class, inversedBy: 'people')]
    private Collection $type;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: PersonCard::class, orphanRemoval: true)]
    private Collection $personCards;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $mobile2 = null;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: PlugRepserviceOrder::class, orphanRemoval: true)]
    private Collection $plugRepserviceOrders;

    /**
     * @var Collection<int, PreInvoiceDoc>
     */
    #[ORM\OneToMany(mappedBy: 'person', targetEntity: PreInvoiceDoc::class, orphanRemoval: true)]
    private Collection $preInvoiceDocs;

    /**
     * @var Collection<int, HesabdariDoc>
     */
    #[ORM\OneToMany(mappedBy: 'salesman', targetEntity: HesabdariDoc::class)]
    private Collection $hesabdariDocs;

    public function __construct()
    {
        $this->hesabdariRows = new ArrayCollection();
        $this->plugNoghreOrders = new ArrayCollection();
        $this->ordersFromCustomer = new ArrayCollection();
        $this->storeroomTickets = new ArrayCollection();
        $this->shareholders = new ArrayCollection();
        $this->cheques = new ArrayCollection();
        $this->type = new ArrayCollection();
        $this->personCards = new ArrayCollection();
        $this->plugRepserviceOrders = new ArrayCollection();
        $this->preInvoiceDocs = new ArrayCollection();
        $this->hesabdariDocs = new ArrayCollection();
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

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getShenasemeli(): ?string
    {
        return $this->shenasemeli;
    }

    public function setShenasemeli(?string $shenasemeli): static
    {
        $this->shenasemeli = $shenasemeli;

        return $this;
    }

    public function getCodeeghtesadi(): ?string
    {
        return $this->codeeghtesadi;
    }

    public function setCodeeghtesadi(?string $codeeghtesadi): static
    {
        $this->codeeghtesadi = $codeeghtesadi;

        return $this;
    }

    public function getSabt(): ?string
    {
        return $this->sabt;
    }

    public function setSabt(?string $sabt): static
    {
        $this->sabt = $sabt;

        return $this;
    }

    public function getKeshvar(): ?string
    {
        return $this->keshvar;
    }

    public function setKeshvar(?string $keshvar): static
    {
        $this->keshvar = $keshvar;

        return $this;
    }

    public function getOstan(): ?string
    {
        return $this->ostan;
    }

    public function setOstan(?string $ostan): static
    {
        $this->ostan = $ostan;

        return $this;
    }

    public function getShahr(): ?string
    {
        return $this->shahr;
    }

    public function setShahr(?string $shahr): static
    {
        $this->shahr = $shahr;

        return $this;
    }

    public function getPostalcode(): ?string
    {
        return $this->postalcode;
    }

    public function setPostalcode(?string $postalcode): static
    {
        $this->postalcode = $postalcode;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): static
    {
        $this->website = $website;

        return $this;
    }

    public function getFax(): ?string
    {
        return $this->fax;
    }

    public function setFax(?string $fax): static
    {
        $this->fax = $fax;

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
            $storeroomTicket->setPerson($this);
        }

        return $this;
    }

    public function removeStoreroomTicket(StoreroomTicket $storeroomTicket): static
    {
        if ($this->storeroomTickets->removeElement($storeroomTicket)) {
            // set the owning side to null (unless already changed)
            if ($storeroomTicket->getPerson() === $this) {
                $storeroomTicket->setPerson(null);
            }
        }

        return $this;
    }

    public function getBirthday(): ?string
    {
        return $this->birthday;
    }

    public function setBirthday(?string $birthday): static
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * @return Collection<int, Shareholder>
     */
    public function getShareholders(): Collection
    {
        return $this->shareholders;
    }

    public function addShareholder(Shareholder $shareholder): static
    {
        if (!$this->shareholders->contains($shareholder)) {
            $this->shareholders->add($shareholder);
            $shareholder->setPerson($this);
        }

        return $this;
    }

    public function removeShareholder(Shareholder $shareholder): static
    {
        if ($this->shareholders->removeElement($shareholder)) {
            // set the owning side to null (unless already changed)
            if ($shareholder->getPerson() === $this) {
                $shareholder->setPerson(null);
            }
        }

        return $this;
    }

    public function isSpeedAccess(): ?bool
    {
        return $this->speedAccess;
    }

    public function setSpeedAccess(?bool $speedAccess): static
    {
        $this->speedAccess = $speedAccess;

        return $this;
    }

    /**
     * @return Collection<int, Cheque>
     */
    public function getCheques(): Collection
    {
        return $this->cheques;
    }

    public function addCheque(Cheque $cheque): static
    {
        if (!$this->cheques->contains($cheque)) {
            $this->cheques->add($cheque);
            $cheque->setPerson($this);
        }

        return $this;
    }

    public function removeCheque(Cheque $cheque): static
    {
        if ($this->cheques->removeElement($cheque)) {
            // set the owning side to null (unless already changed)
            if ($cheque->getPerson() === $this) {
                $cheque->setPerson(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PersonType>
     */
    public function getType(): Collection
    {
        return $this->type;
    }

    public function addType(PersonType $type): static
    {
        if (!$this->type->contains($type)) {
            $this->type->add($type);
        }

        return $this;
    }

    public function removeType(PersonType $type): static
    {
        $this->type->removeElement($type);

        return $this;
    }

    /**
     * @return Collection<int, PersonCard>
     */
    public function getPersonCards(): Collection
    {
        return $this->personCards;
    }

    public function addPersonCard(PersonCard $personCard): static
    {
        if (!$this->personCards->contains($personCard)) {
            $this->personCards->add($personCard);
            $personCard->setPerson($this);
        }

        return $this;
    }

    public function removePersonCard(PersonCard $personCard): static
    {
        if ($this->personCards->removeElement($personCard)) {
            // set the owning side to null (unless already changed)
            if ($personCard->getPerson() === $this) {
                $personCard->setPerson(null);
            }
        }

        return $this;
    }

    public function getMobile2(): ?string
    {
        return $this->mobile2;
    }

    public function setMobile2(?string $mobile2): static
    {
        $this->mobile2 = $mobile2;

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
            $plugRepserviceOrder->setPerson($this);
        }

        return $this;
    }

    public function removePlugRepserviceOrder(PlugRepserviceOrder $plugRepserviceOrder): static
    {
        if ($this->plugRepserviceOrders->removeElement($plugRepserviceOrder)) {
            // set the owning side to null (unless already changed)
            if ($plugRepserviceOrder->getPerson() === $this) {
                $plugRepserviceOrder->setPerson(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PreInvoiceDoc>
     */
    public function getPreInvoiceDocs(): Collection
    {
        return $this->preInvoiceDocs;
    }

    public function addPreInvoiceDoc(PreInvoiceDoc $preInvoiceDoc): static
    {
        if (!$this->preInvoiceDocs->contains($preInvoiceDoc)) {
            $this->preInvoiceDocs->add($preInvoiceDoc);
            $preInvoiceDoc->setPerson($this);
        }

        return $this;
    }

    public function removePreInvoiceDoc(PreInvoiceDoc $preInvoiceDoc): static
    {
        if ($this->preInvoiceDocs->removeElement($preInvoiceDoc)) {
            // set the owning side to null (unless already changed)
            if ($preInvoiceDoc->getPerson() === $this) {
                $preInvoiceDoc->setPerson(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, HesabdariDoc>
     */
    public function getHesabdariDocs(): Collection
    {
        return $this->hesabdariDocs;
    }

    public function addHesabdariDoc(HesabdariDoc $hesabdariDoc): static
    {
        if (!$this->hesabdariDocs->contains($hesabdariDoc)) {
            $this->hesabdariDocs->add($hesabdariDoc);
            $hesabdariDoc->setSalesman($this);
        }

        return $this;
    }

    public function removeHesabdariDoc(HesabdariDoc $hesabdariDoc): static
    {
        if ($this->hesabdariDocs->removeElement($hesabdariDoc)) {
            // set the owning side to null (unless already changed)
            if ($hesabdariDoc->getSalesman() === $this) {
                $hesabdariDoc->setSalesman(null);
            }
        }

        return $this;
    }
}
