<?php

namespace App\Entity;

use App\Repository\BankAccountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\Entity(repositoryClass: BankAccountRepository::class)]
class BankAccount
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'bankAccounts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Ignore]
    private ?Business $bid = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cardNum = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shaba = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $accountNum = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $owner = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shobe = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $posNum = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $des = null;

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $mobileInternetBank = null;

    #[ORM\Column(length: 255)]
    private ?string $code = null;

    #[ORM\OneToMany(mappedBy: 'bank', targetEntity: HesabdariRow::class)]
    #[Ignore]
    private Collection $hesabdariRows;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $balance = null;

    #[ORM\OneToMany(mappedBy: 'bank', targetEntity: Cheque::class)]
    private Collection $cheques;

    public function __construct()
    {
        $this->hesabdariRows = new ArrayCollection();
        $this->cheques = new ArrayCollection();
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCardNum(): ?string
    {
        return $this->cardNum;
    }

    public function setCardNum(?string $cardNum): self
    {
        $this->cardNum = $cardNum;

        return $this;
    }

    public function getShaba(): ?string
    {
        return $this->shaba;
    }

    public function setShaba(?string $shaba): self
    {
        $this->shaba = $shaba;

        return $this;
    }

    public function getAccountNum(): ?string
    {
        return $this->accountNum;
    }

    public function setAccountNum(?string $accountNum): self
    {
        $this->accountNum = $accountNum;

        return $this;
    }

    public function getOwner(): ?string
    {
        return $this->owner;
    }

    public function setOwner(?string $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getShobe(): ?string
    {
        return $this->shobe;
    }

    public function setShobe(?string $shobe): self
    {
        $this->shobe = $shobe;

        return $this;
    }

    public function getPosNum(): ?string
    {
        return $this->posNum;
    }

    public function setPosNum(?string $posNum): self
    {
        $this->posNum = $posNum;

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

    public function getMobileInternetBank(): ?string
    {
        return $this->mobileInternetBank;
    }

    public function setMobileInternetBank(?string $mobileInternetBank): self
    {
        $this->mobileInternetBank = $mobileInternetBank;

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
            $hesabdariRow->setBank($this);
        }

        return $this;
    }

    public function removeHesabdariRow(HesabdariRow $hesabdariRow): self
    {
        if ($this->hesabdariRows->removeElement($hesabdariRow)) {
            // set the owning side to null (unless already changed)
            if ($hesabdariRow->getBank() === $this) {
                $hesabdariRow->setBank(null);
            }
        }

        return $this;
    }

    public function getBalance(): ?string
    {
        return $this->balance;
    }

    public function setBalance(?string $balance): static
    {
        $this->balance = $balance;

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
            $cheque->setBank($this);
        }

        return $this;
    }

    public function removeCheque(Cheque $cheque): static
    {
        if ($this->cheques->removeElement($cheque)) {
            // set the owning side to null (unless already changed)
            if ($cheque->getBank() === $this) {
                $cheque->setBank(null);
            }
        }

        return $this;
    }
}
