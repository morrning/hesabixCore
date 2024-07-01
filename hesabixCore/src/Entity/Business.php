<?php

namespace App\Entity;

use App\Repository\BusinessRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\Entity(repositoryClass: BusinessRepository::class)]
class Business
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $legalName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $field = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shenasemeli = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $codeeghtesadi = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shomaresabt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ostan = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shahrestan = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $postalcode = null;

    #[ORM\Column(length: 12, nullable: true)]
    private ?string $tel = null;

    #[ORM\Column(length: 12, nullable: true)]
    private ?string $mobile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $wesite = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\ManyToOne(inversedBy: 'businesses')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Money $money = null;

    #[ORM\Column(length: 255)]
    private ?string $maliyatafzode = null;

    #[ORM\Column(length: 255)]
    private ?string $dateSubmit = null;

    #[ORM\OneToMany(mappedBy: 'bid', targetEntity: Log::class)]
    private Collection $logs;

    #[ORM\OneToMany(mappedBy: 'bid', targetEntity: Permission::class)]
    private Collection $permissions;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?string $personCode = null;

    #[ORM\OneToMany(mappedBy: 'bid', targetEntity: Person::class, orphanRemoval: true)]
    private Collection $people;

    #[ORM\OneToMany(mappedBy: 'bid', targetEntity: Year::class, orphanRemoval: true)]
    private Collection $years;

    #[ORM\OneToMany(mappedBy: 'bid', targetEntity: BankAccount::class, orphanRemoval: true)]
    private Collection $bankAccounts;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?string $bankCode = null;

    #[ORM\OneToMany(mappedBy: 'bid', targetEntity: HesabdariDoc::class)]
    private Collection $hesabdariDocs;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $receiveCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $accountingCode = null;

    #[ORM\OneToMany(mappedBy: 'bid', targetEntity: HesabdariRow::class, orphanRemoval: true)]
    private Collection $hesabdariRows;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?string $CommodityCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $salaryCode = '1000';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cashdeskCode = '1000';

    #[ORM\OneToMany(mappedBy: 'bid', targetEntity: Salary::class, orphanRemoval: true)]
    private Collection $salaries;

    #[ORM\OneToMany(mappedBy: 'bid', targetEntity: Cashdesk::class)]
    private Collection $cashdesks;

    #[ORM\OneToMany(mappedBy: 'bid', targetEntity: Notification::class, orphanRemoval: true)]
    private Collection $notifications;

    #[ORM\OneToMany(mappedBy: 'bid', targetEntity: Plugin::class, orphanRemoval: true)]
    private Collection $plugins;

    #[ORM\OneToMany(mappedBy: 'bid', targetEntity: PlugNoghreOrder::class, orphanRemoval: true)]
    private Collection $plugNoghreOrders;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $zarinpalCode = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column(nullable: true)]
    private ?bool $storeOnline = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $storeUsername = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $smsCharge = null;

    #[ORM\OneToMany(mappedBy: 'bid', targetEntity: SMSPays::class, orphanRemoval: true)]
    private Collection $sMSPays;

    #[ORM\OneToMany(mappedBy: 'bid', targetEntity: SMSSettings::class, orphanRemoval: true)]
    private Collection $sMSSettings;

    #[ORM\OneToMany(mappedBy: 'bid', targetEntity: CommodityDrop::class)]
    private Collection $commodityDrops;

    #[ORM\OneToMany(mappedBy: 'bid', targetEntity: Storeroom::class, orphanRemoval: true)]
    private Collection $storerooms;

    #[ORM\Column(nullable: true)]
    private ?bool $shortlinks = false;

    #[ORM\OneToMany(mappedBy: 'bid', targetEntity: WalletTransaction::class, orphanRemoval: true)]
    #[Ignore]
    private Collection $walletTransactions;

    #[ORM\ManyToOne]
    #[Ignore]
    private ?BankAccount $WalletMatchBank = null;

    #[ORM\Column(nullable: true)]
    #[Ignore]
    private ?bool $walletEnable = null;

    #[ORM\OneToMany(mappedBy: 'bid', targetEntity: StoreroomTicket::class, orphanRemoval: true)]
    #[Ignore]
    private Collection $storeroomTickets;

    #[ORM\OneToMany(mappedBy: 'bid', targetEntity: StoreroomItem::class, orphanRemoval: true)]
    #[Ignore]
    private Collection $storeroomItems;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $storeroomCode = '1000';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $archiveSize = null;

    #[ORM\OneToMany(mappedBy: 'bid', targetEntity: ArchiveFile::class, orphanRemoval: true)]
    private Collection $archiveFiles;

    #[ORM\OneToMany(mappedBy: 'bid', targetEntity: ArchiveOrders::class, orphanRemoval: true)]
    private Collection $archiveOrders;

    #[ORM\OneToMany(mappedBy: 'bid', targetEntity: Shareholder::class, orphanRemoval: true)]
    private Collection $shareholders;

    #[ORM\OneToMany(mappedBy: 'bid', targetEntity: Hook::class, orphanRemoval: true)]
    private Collection $hooks;

    #[ORM\OneToMany(mappedBy: 'bid', targetEntity: Cheque::class, orphanRemoval: true)]
    private Collection $cheques;

    #[ORM\OneToMany(mappedBy: 'bid', targetEntity: PersonCard::class, orphanRemoval: true)]
    private Collection $personCards;

    #[ORM\OneToMany(mappedBy: 'bid', targetEntity: MostDes::class, orphanRemoval: true)]
    private Collection $mostDes;

    #[ORM\OneToMany(mappedBy: 'bid', targetEntity: PlugRepserviceOrder::class, orphanRemoval: true)]
    private Collection $plugRepserviceOrders;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $plugRepserviceCode = null;

    #[ORM\OneToMany(mappedBy: 'bid', targetEntity: Printer::class, orphanRemoval: true)]
    private Collection $printers;

    #[ORM\OneToMany(mappedBy: 'bid', targetEntity: PrintTemplate::class, orphanRemoval: true)]
    private Collection $printTemplates;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatar = null;

    public function __construct()
    {
        $this->logs = new ArrayCollection();
        $this->permissions = new ArrayCollection();
        $this->people = new ArrayCollection();
        $this->years = new ArrayCollection();
        $this->bankAccounts = new ArrayCollection();
        $this->hesabdariDocs = new ArrayCollection();
        $this->hesabdariRows = new ArrayCollection();
        $this->salaries = new ArrayCollection();
        $this->cashdesks = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->plugins = new ArrayCollection();
        $this->plugNoghreOrders = new ArrayCollection();
        $this->sMSPays = new ArrayCollection();
        $this->sMSSettings = new ArrayCollection();
        $this->commodityDrops = new ArrayCollection();
        $this->storerooms = new ArrayCollection();
        $this->walletTransactions = new ArrayCollection();
        $this->storeroomTickets = new ArrayCollection();
        $this->storeroomItems = new ArrayCollection();
        $this->archiveFiles = new ArrayCollection();
        $this->archiveOrders = new ArrayCollection();
        $this->shareholders = new ArrayCollection();
        $this->hooks = new ArrayCollection();
        $this->cheques = new ArrayCollection();
        $this->personCards = new ArrayCollection();
        $this->mostDes = new ArrayCollection();
        $this->plugRepserviceOrders = new ArrayCollection();
        $this->printers = new ArrayCollection();
        $this->printTemplates = new ArrayCollection();
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

    public function getLegalName(): ?string
    {
        return $this->legalName;
    }

    public function setLegalName(string $legalName): self
    {
        $this->legalName = $legalName;

        return $this;
    }

    public function getField(): ?string
    {
        return $this->field;
    }

    public function setField(?string $field): self
    {
        $this->field = $field;

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

    public function getShenasemeli(): ?string
    {
        return $this->shenasemeli;
    }

    public function setShenasemeli(?string $shenasemeli): self
    {
        $this->shenasemeli = $shenasemeli;

        return $this;
    }

    public function getCodeeghtesadi(): ?string
    {
        return $this->codeeghtesadi;
    }

    public function setCodeeghtesadi(?string $codeeghtesadi): self
    {
        $this->codeeghtesadi = $codeeghtesadi;

        return $this;
    }

    public function getShomaresabt(): ?string
    {
        return $this->shomaresabt;
    }

    public function setShomaresabt(?string $shomaresabt): self
    {
        $this->shomaresabt = $shomaresabt;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getOstan(): ?string
    {
        return $this->ostan;
    }

    public function setOstan(?string $ostan): self
    {
        $this->ostan = $ostan;

        return $this;
    }

    public function getShahrestan(): ?string
    {
        return $this->shahrestan;
    }

    public function setShahrestan(?string $shahrestan): self
    {
        $this->shahrestan = $shahrestan;

        return $this;
    }

    public function getPostalcode(): ?string
    {
        return $this->postalcode;
    }

    public function setPostalcode(?string $postalcode): self
    {
        $this->postalcode = $postalcode;

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

    public function getWesite(): ?string
    {
        return $this->wesite;
    }

    public function setWesite(?string $wesite): self
    {
        $this->wesite = $wesite;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

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

    public function getMaliyatafzode(): ?string
    {
        return $this->maliyatafzode;
    }

    public function setMaliyatafzode(string $maliyatafzode): self
    {
        $this->maliyatafzode = $maliyatafzode;

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

    /**
     * @return Collection<int, Log>
     */
    public function getLogs(): Collection
    {
        return $this->logs;
    }

    public function addLog(Log $log): self
    {
        if (!$this->logs->contains($log)) {
            $this->logs->add($log);
            $log->setBid($this);
        }

        return $this;
    }

    public function removeLog(Log $log): self
    {
        if ($this->logs->removeElement($log)) {
            // set the owning side to null (unless already changed)
            if ($log->getBid() === $this) {
                $log->setBid(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Permission>
     */
    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    public function addPermission(Permission $permission): self
    {
        if (!$this->permissions->contains($permission)) {
            $this->permissions->add($permission);
            $permission->setBid($this);
        }

        return $this;
    }

    public function removePermission(Permission $permission): self
    {
        if ($this->permissions->removeElement($permission)) {
            // set the owning side to null (unless already changed)
            if ($permission->getBid() === $this) {
                $permission->setBid(null);
            }
        }

        return $this;
    }

    public function getPersonCode(): ?string
    {
        return $this->personCode;
    }

    public function setPersonCode(string $personCode): self
    {
        $this->personCode = $personCode;

        return $this;
    }

    /**
     * @return Collection<int, Person>
     */
    public function getPeople(): Collection
    {
        return $this->people;
    }

    public function addPerson(Person $person): self
    {
        if (!$this->people->contains($person)) {
            $this->people->add($person);
            $person->setBid($this);
        }

        return $this;
    }

    public function removePerson(Person $person): self
    {
        if ($this->people->removeElement($person)) {
            // set the owning side to null (unless already changed)
            if ($person->getBid() === $this) {
                $person->setBid(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Year>
     */
    public function getYears(): Collection
    {
        return $this->years;
    }

    public function addYear(Year $year): self
    {
        if (!$this->years->contains($year)) {
            $this->years->add($year);
            $year->setBid($this);
        }

        return $this;
    }

    public function removeYear(Year $year): self
    {
        if ($this->years->removeElement($year)) {
            // set the owning side to null (unless already changed)
            if ($year->getBid() === $this) {
                $year->setBid(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BankAccount>
     */
    public function getBankAccounts(): Collection
    {
        return $this->bankAccounts;
    }

    public function addBankAccount(BankAccount $bankAccount): self
    {
        if (!$this->bankAccounts->contains($bankAccount)) {
            $this->bankAccounts->add($bankAccount);
            $bankAccount->setBid($this);
        }

        return $this;
    }

    public function removeBankAccount(BankAccount $bankAccount): self
    {
        if ($this->bankAccounts->removeElement($bankAccount)) {
            // set the owning side to null (unless already changed)
            if ($bankAccount->getBid() === $this) {
                $bankAccount->setBid(null);
            }
        }

        return $this;
    }

    public function getBankCode(): ?string
    {
        return $this->bankCode;
    }

    public function setBankCode(?string $bankCode): self
    {
        $this->bankCode = $bankCode;

        return $this;
    }

    /**
     * @return Collection<int, HesabdariDoc>
     */
    public function getHesabdariDocs(): Collection
    {
        return $this->hesabdariDocs;
    }

    public function addHesabdariDoc(HesabdariDoc $hesabdariDoc): self
    {
        if (!$this->hesabdariDocs->contains($hesabdariDoc)) {
            $this->hesabdariDocs->add($hesabdariDoc);
            $hesabdariDoc->setBid($this);
        }

        return $this;
    }

    public function removeHesabdariDoc(HesabdariDoc $hesabdariDoc): self
    {
        if ($this->hesabdariDocs->removeElement($hesabdariDoc)) {
            // set the owning side to null (unless already changed)
            if ($hesabdariDoc->getBid() === $this) {
                $hesabdariDoc->setBid(null);
            }
        }

        return $this;
    }
    public function getReceiveCode(): ?string
    {
        return $this->receiveCode;
    }

    public function setReceiveCode(?string $receiveCode): self
    {
        $this->receiveCode = $receiveCode;

        return $this;
    }

    public function getAccountingCode(): ?string
    {
        return $this->accountingCode;
    }

    public function setAccountingCode(?string $accountingCode): self
    {
        $this->accountingCode = $accountingCode;

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
            $hesabdariRow->setBid($this);
        }

        return $this;
    }

    public function removeHesabdariRow(HesabdariRow $hesabdariRow): self
    {
        if ($this->hesabdariRows->removeElement($hesabdariRow)) {
            // set the owning side to null (unless already changed)
            if ($hesabdariRow->getBid() === $this) {
                $hesabdariRow->setBid(null);
            }
        }

        return $this;
    }

    public function getCommodityCode(): ?string
    {
        return $this->CommodityCode;
    }

    public function setCommodityCode(?string $CommodityCode): self
    {
        $this->CommodityCode = $CommodityCode;

        return $this;
    }

    public function getSalaryCode(): ?string
    {
        return $this->salaryCode;
    }

    public function setSalaryCode(?string $salaryCode): self
    {
        $this->salaryCode = $salaryCode;

        return $this;
    }

    public function getCashdeskCode(): ?string
    {
        return $this->cashdeskCode;
    }

    public function setCashdeskCode(string $cashdeskCode): self
    {
        $this->cashdeskCode = $cashdeskCode;

        return $this;
    }

    /**
     * @return Collection<int, Salary>
     */
    public function getSalaries(): Collection
    {
        return $this->salaries;
    }

    public function addSalary(Salary $salary): self
    {
        if (!$this->salaries->contains($salary)) {
            $this->salaries->add($salary);
            $salary->setBid($this);
        }

        return $this;
    }

    public function removeSalary(Salary $salary): self
    {
        if ($this->salaries->removeElement($salary)) {
            // set the owning side to null (unless already changed)
            if ($salary->getBid() === $this) {
                $salary->setBid(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Cashdesk>
     */
    public function getCashdesks(): Collection
    {
        return $this->cashdesks;
    }

    public function addCashdesk(Cashdesk $cashdesk): self
    {
        if (!$this->cashdesks->contains($cashdesk)) {
            $this->cashdesks->add($cashdesk);
            $cashdesk->setBid($this);
        }

        return $this;
    }

    public function removeCashdesk(Cashdesk $cashdesk): self
    {
        if ($this->cashdesks->removeElement($cashdesk)) {
            // set the owning side to null (unless already changed)
            if ($cashdesk->getBid() === $this) {
                $cashdesk->setBid(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setBid($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getBid() === $this) {
                $notification->setBid(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Plugin>
     */
    public function getPlugins(): Collection
    {
        return $this->plugins;
    }

    public function addPlugin(Plugin $plugin): static
    {
        if (!$this->plugins->contains($plugin)) {
            $this->plugins->add($plugin);
            $plugin->setBid($this);
        }

        return $this;
    }

    public function removePlugin(Plugin $plugin): static
    {
        if ($this->plugins->removeElement($plugin)) {
            // set the owning side to null (unless already changed)
            if ($plugin->getBid() === $this) {
                $plugin->setBid(null);
            }
        }

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
            $plugNoghreOrder->setBid($this);
        }

        return $this;
    }

    public function removePlugNoghreOrder(PlugNoghreOrder $plugNoghreOrder): static
    {
        if ($this->plugNoghreOrders->removeElement($plugNoghreOrder)) {
            // set the owning side to null (unless already changed)
            if ($plugNoghreOrder->getBid() === $this) {
                $plugNoghreOrder->setBid(null);
            }
        }

        return $this;
    }

    public function getZarinpalCode(): ?string
    {
        return $this->zarinpalCode;
    }

    public function setZarinpalCode(?string $zarinpalCode): static
    {
        $this->zarinpalCode = $zarinpalCode;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function isStoreOnline(): ?bool
    {
        return $this->storeOnline;
    }

    public function setStoreOnline(?bool $storeOnline): static
    {
        $this->storeOnline = $storeOnline;

        return $this;
    }

    public function getStoreUsername(): ?string
    {
        return $this->storeUsername;
    }

    public function setStoreUsername(?string $storeUsername): static
    {
        $this->storeUsername = $storeUsername;

        return $this;
    }

    public function getSmsCharge(): ?string
    {
        if(!$this->smsCharge) return 0;
        return $this->smsCharge;
    }

    public function setSmsCharge(?string $smsCharge): static
    {
        $this->smsCharge = $smsCharge;

        return $this;
    }

    /**
     * @return Collection<int, SMSPays>
     */
    public function getSMSPays(): Collection
    {
        return $this->sMSPays;
    }

    public function addSMSPay(SMSPays $sMSPay): static
    {
        if (!$this->sMSPays->contains($sMSPay)) {
            $this->sMSPays->add($sMSPay);
            $sMSPay->setBid($this);
        }

        return $this;
    }

    public function removeSMSPay(SMSPays $sMSPay): static
    {
        if ($this->sMSPays->removeElement($sMSPay)) {
            // set the owning side to null (unless already changed)
            if ($sMSPay->getBid() === $this) {
                $sMSPay->setBid(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, SMSSettings>
     */
    public function getSMSSettings(): Collection
    {
        return $this->sMSSettings;
    }

    public function addSMSSetting(SMSSettings $sMSSetting): static
    {
        if (!$this->sMSSettings->contains($sMSSetting)) {
            $this->sMSSettings->add($sMSSetting);
            $sMSSetting->setBid($this);
        }

        return $this;
    }

    public function removeSMSSetting(SMSSettings $sMSSetting): static
    {
        if ($this->sMSSettings->removeElement($sMSSetting)) {
            // set the owning side to null (unless already changed)
            if ($sMSSetting->getBid() === $this) {
                $sMSSetting->setBid(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CommodityDrop>
     */
    public function getCommodityDrops(): Collection
    {
        return $this->commodityDrops;
    }

    public function addCommodityDrop(CommodityDrop $commodityDrop): static
    {
        if (!$this->commodityDrops->contains($commodityDrop)) {
            $this->commodityDrops->add($commodityDrop);
            $commodityDrop->setBid($this);
        }

        return $this;
    }

    public function removeCommodityDrop(CommodityDrop $commodityDrop): static
    {
        if ($this->commodityDrops->removeElement($commodityDrop)) {
            // set the owning side to null (unless already changed)
            if ($commodityDrop->getBid() === $this) {
                $commodityDrop->setBid(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Storeroom>
     */
    public function getStorerooms(): Collection
    {
        return $this->storerooms;
    }

    public function addStoreroom(Storeroom $storeroom): static
    {
        if (!$this->storerooms->contains($storeroom)) {
            $this->storerooms->add($storeroom);
            $storeroom->setBid($this);
        }

        return $this;
    }

    public function removeStoreroom(Storeroom $storeroom): static
    {
        if ($this->storerooms->removeElement($storeroom)) {
            // set the owning side to null (unless already changed)
            if ($storeroom->getBid() === $this) {
                $storeroom->setBid(null);
            }
        }

        return $this;
    }

    public function isShortlinks(): ?bool
    {   
        if($this->shortlinks == true) return true;
        return false;
    }

    public function setShortlinks(?bool $shortlinks): static
    {
        $this->shortlinks = $shortlinks;

        return $this;
    }

    /**
     * @return Collection<int, WalletTransaction>
     */
    public function getWalletTransactions(): Collection
    {
        return $this->walletTransactions;
    }

    public function addWalletTransaction(WalletTransaction $walletTransaction): static
    {
        if (!$this->walletTransactions->contains($walletTransaction)) {
            $this->walletTransactions->add($walletTransaction);
            $walletTransaction->setBid($this);
        }

        return $this;
    }

    public function removeWalletTransaction(WalletTransaction $walletTransaction): static
    {
        if ($this->walletTransactions->removeElement($walletTransaction)) {
            // set the owning side to null (unless already changed)
            if ($walletTransaction->getBid() === $this) {
                $walletTransaction->setBid(null);
            }
        }

        return $this;
    }

    public function getWalletMatchBank(): ?BankAccount
    {
        return $this->WalletMatchBank;
    }

    public function setWalletMatchBank(?BankAccount $WalletMatchBank): static
    {
        $this->WalletMatchBank = $WalletMatchBank;

        return $this;
    }

    public function isWalletEnable(): ?bool
    {
        return $this->walletEnable;
    }

    public function setWalletEnable(?bool $walletEnable): static
    {
        $this->walletEnable = $walletEnable;

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
            $storeroomTicket->setBid($this);
        }

        return $this;
    }

    public function removeStoreroomTicket(StoreroomTicket $storeroomTicket): static
    {
        if ($this->storeroomTickets->removeElement($storeroomTicket)) {
            // set the owning side to null (unless already changed)
            if ($storeroomTicket->getBid() === $this) {
                $storeroomTicket->setBid(null);
            }
        }

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
            $storeroomItem->setBid($this);
        }

        return $this;
    }

    public function removeStoreroomItem(StoreroomItem $storeroomItem): static
    {
        if ($this->storeroomItems->removeElement($storeroomItem)) {
            // set the owning side to null (unless already changed)
            if ($storeroomItem->getBid() === $this) {
                $storeroomItem->setBid(null);
            }
        }

        return $this;
    }

    public function getStoreroomCode(): ?string
    {
        return $this->storeroomCode;
    }

    public function setStoreroomCode(?string $storeroomCode): static
    {
        $this->storeroomCode = $storeroomCode;

        return $this;
    }

    public function getArchiveSize(): ?string
    {
        return $this->archiveSize;
    }

    public function setArchiveSize(?string $archiveSize): static
    {
        $this->archiveSize = $archiveSize;

        return $this;
    }

    /**
     * @return Collection<int, ArchiveFile>
     */
    public function getArchiveFiles(): Collection
    {
        return $this->archiveFiles;
    }

    public function addArchiveFile(ArchiveFile $archiveFile): static
    {
        if (!$this->archiveFiles->contains($archiveFile)) {
            $this->archiveFiles->add($archiveFile);
            $archiveFile->setBid($this);
        }

        return $this;
    }

    public function removeArchiveFile(ArchiveFile $archiveFile): static
    {
        if ($this->archiveFiles->removeElement($archiveFile)) {
            // set the owning side to null (unless already changed)
            if ($archiveFile->getBid() === $this) {
                $archiveFile->setBid(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ArchiveOrders>
     */
    public function getArchiveOrders(): Collection
    {
        return $this->archiveOrders;
    }

    public function addArchiveOrder(ArchiveOrders $archiveOrder): static
    {
        if (!$this->archiveOrders->contains($archiveOrder)) {
            $this->archiveOrders->add($archiveOrder);
            $archiveOrder->setBid($this);
        }

        return $this;
    }

    public function removeArchiveOrder(ArchiveOrders $archiveOrder): static
    {
        if ($this->archiveOrders->removeElement($archiveOrder)) {
            // set the owning side to null (unless already changed)
            if ($archiveOrder->getBid() === $this) {
                $archiveOrder->setBid(null);
            }
        }

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
            $shareholder->setBid($this);
        }

        return $this;
    }

    public function removeShareholder(Shareholder $shareholder): static
    {
        if ($this->shareholders->removeElement($shareholder)) {
            // set the owning side to null (unless already changed)
            if ($shareholder->getBid() === $this) {
                $shareholder->setBid(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Hook>
     */
    public function getHooks(): Collection
    {
        return $this->hooks;
    }

    public function addHook(Hook $hook): static
    {
        if (!$this->hooks->contains($hook)) {
            $this->hooks->add($hook);
            $hook->setBid($this);
        }

        return $this;
    }

    public function removeHook(Hook $hook): static
    {
        if ($this->hooks->removeElement($hook)) {
            // set the owning side to null (unless already changed)
            if ($hook->getBid() === $this) {
                $hook->setBid(null);
            }
        }

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
            $cheque->setBid($this);
        }

        return $this;
    }

    public function removeCheque(Cheque $cheque): static
    {
        if ($this->cheques->removeElement($cheque)) {
            // set the owning side to null (unless already changed)
            if ($cheque->getBid() === $this) {
                $cheque->setBid(null);
            }
        }

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
            $personCard->setBid($this);
        }

        return $this;
    }

    public function removePersonCard(PersonCard $personCard): static
    {
        if ($this->personCards->removeElement($personCard)) {
            // set the owning side to null (unless already changed)
            if ($personCard->getBid() === $this) {
                $personCard->setBid(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MostDes>
     */
    public function getMostDes(): Collection
    {
        return $this->mostDes;
    }

    public function addMostDe(MostDes $mostDe): static
    {
        if (!$this->mostDes->contains($mostDe)) {
            $this->mostDes->add($mostDe);
            $mostDe->setBid($this);
        }

        return $this;
    }

    public function removeMostDe(MostDes $mostDe): static
    {
        if ($this->mostDes->removeElement($mostDe)) {
            // set the owning side to null (unless already changed)
            if ($mostDe->getBid() === $this) {
                $mostDe->setBid(null);
            }
        }

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
            $plugRepserviceOrder->setBid($this);
        }

        return $this;
    }

    public function removePlugRepserviceOrder(PlugRepserviceOrder $plugRepserviceOrder): static
    {
        if ($this->plugRepserviceOrders->removeElement($plugRepserviceOrder)) {
            // set the owning side to null (unless already changed)
            if ($plugRepserviceOrder->getBid() === $this) {
                $plugRepserviceOrder->setBid(null);
            }
        }

        return $this;
    }

    public function getPlugRepserviceCode(): ?string
    {
        return $this->plugRepserviceCode;
    }

    public function setPlugRepserviceCode(?string $plugRepserviceCode): static
    {
        $this->plugRepserviceCode = $plugRepserviceCode;

        return $this;
    }

    /**
     * @return Collection<int, Printer>
     */
    public function getPrinters(): Collection
    {
        return $this->printers;
    }

    public function addPrinter(Printer $printer): static
    {
        if (!$this->printers->contains($printer)) {
            $this->printers->add($printer);
            $printer->setBid($this);
        }

        return $this;
    }

    public function removePrinter(Printer $printer): static
    {
        if ($this->printers->removeElement($printer)) {
            // set the owning side to null (unless already changed)
            if ($printer->getBid() === $this) {
                $printer->setBid(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PrintTemplate>
     */
    public function getPrintTemplates(): Collection
    {
        return $this->printTemplates;
    }

    public function addPrintTemplate(PrintTemplate $printTemplate): static
    {
        if (!$this->printTemplates->contains($printTemplate)) {
            $this->printTemplates->add($printTemplate);
            $printTemplate->setBid($this);
        }

        return $this;
    }

    public function removePrintTemplate(PrintTemplate $printTemplate): static
    {
        if ($this->printTemplates->removeElement($printTemplate)) {
            // set the owning side to null (unless already changed)
            if ($printTemplate->getBid() === $this) {
                $printTemplate->setBid(null);
            }
        }

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): static
    {
        $this->avatar = $avatar;

        return $this;
    }
}
