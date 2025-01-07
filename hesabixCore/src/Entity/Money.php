<?php

namespace App\Entity;

use App\Repository\MoneyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MoneyRepository::class)]
class Money
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\OneToMany(mappedBy: 'money', targetEntity: Business::class, orphanRemoval: true)]
    private Collection $businesses;

    #[ORM\OneToMany(mappedBy: 'money', targetEntity: PriceListDetail::class, orphanRemoval: true)]
    private Collection $priceListDetails;

    /**
     * @var Collection<int, Business>
     */
    #[ORM\ManyToMany(targetEntity: Business::class, mappedBy: 'extraMoney')]
    private Collection $bids;

    #[ORM\Column(length: 255)]
    private ?string $symbol = null;

    #[ORM\Column(length: 255)]
    private ?string $shortName = null;

    /**
     * @var Collection<int, Cashdesk>
     */
    #[ORM\OneToMany(mappedBy: 'money', targetEntity: Cashdesk::class)]
    private Collection $cashdesks;

    /**
     * @var Collection<int, Salary>
     */
    #[ORM\OneToMany(mappedBy: 'money', targetEntity: Salary::class)]
    private Collection $salaries;

    /**
     * @var Collection<int, PreInvoiceDoc>
     */
    #[ORM\OneToMany(mappedBy: 'money', targetEntity: PreInvoiceDoc::class, orphanRemoval: true)]
    private Collection $preInvoiceDocs;

    public function __construct()
    {
        $this->businesses = new ArrayCollection();
        $this->priceListDetails = new ArrayCollection();
        $this->bids = new ArrayCollection();
        $this->cashdesks = new ArrayCollection();
        $this->salaries = new ArrayCollection();
        $this->preInvoiceDocs = new ArrayCollection();
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

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return Collection<int, Business>
     */
    public function getBusinesses(): Collection
    {
        return $this->businesses;
    }

    public function addBusiness(Business $business): self
    {
        if (!$this->businesses->contains($business)) {
            $this->businesses->add($business);
            $business->setMoney($this);
        }

        return $this;
    }

    public function removeBusiness(Business $business): self
    {
        if ($this->businesses->removeElement($business)) {
            // set the owning side to null (unless already changed)
            if ($business->getMoney() === $this) {
                $business->setMoney(null);
            }
        }

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
            $priceListDetail->setMoney($this);
        }

        return $this;
    }

    public function removePriceListDetail(PriceListDetail $priceListDetail): static
    {
        if ($this->priceListDetails->removeElement($priceListDetail)) {
            // set the owning side to null (unless already changed)
            if ($priceListDetail->getMoney() === $this) {
                $priceListDetail->setMoney(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Business>
     */
    public function getBids(): Collection
    {
        return $this->bids;
    }

    public function addBid(Business $bid): static
    {
        if (!$this->bids->contains($bid)) {
            $this->bids->add($bid);
            $bid->addExtraMoney($this);
        }

        return $this;
    }

    public function removeBid(Business $bid): static
    {
        if ($this->bids->removeElement($bid)) {
            $bid->removeExtraMoney($this);
        }

        return $this;
    }

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(string $symbol): static
    {
        $this->symbol = $symbol;

        return $this;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(string $shortName): static
    {
        $this->shortName = $shortName;

        return $this;
    }

    /**
     * @return Collection<int, Cashdesk>
     */
    public function getCashdesks(): Collection
    {
        return $this->cashdesks;
    }

    public function addCashdesk(Cashdesk $cashdesk): static
    {
        if (!$this->cashdesks->contains($cashdesk)) {
            $this->cashdesks->add($cashdesk);
            $cashdesk->setMoney($this);
        }

        return $this;
    }

    public function removeCashdesk(Cashdesk $cashdesk): static
    {
        if ($this->cashdesks->removeElement($cashdesk)) {
            // set the owning side to null (unless already changed)
            if ($cashdesk->getMoney() === $this) {
                $cashdesk->setMoney(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Salary>
     */
    public function getSalaries(): Collection
    {
        return $this->salaries;
    }

    public function addSalary(Salary $salary): static
    {
        if (!$this->salaries->contains($salary)) {
            $this->salaries->add($salary);
            $salary->setMoney($this);
        }

        return $this;
    }

    public function removeSalary(Salary $salary): static
    {
        if ($this->salaries->removeElement($salary)) {
            // set the owning side to null (unless already changed)
            if ($salary->getMoney() === $this) {
                $salary->setMoney(null);
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
            $preInvoiceDoc->setMoney($this);
        }

        return $this;
    }

    public function removePreInvoiceDoc(PreInvoiceDoc $preInvoiceDoc): static
    {
        if ($this->preInvoiceDocs->removeElement($preInvoiceDoc)) {
            // set the owning side to null (unless already changed)
            if ($preInvoiceDoc->getMoney() === $this) {
                $preInvoiceDoc->setMoney(null);
            }
        }

        return $this;
    }
}
