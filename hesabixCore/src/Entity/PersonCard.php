<?php

namespace App\Entity;

use App\Repository\PersonCardRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PersonCardRepository::class)]
class PersonCard
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'personCards')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Business $bid = null;

    #[ORM\ManyToOne(inversedBy: 'personCards')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Person $person = null;

    #[ORM\Column(length: 255)]
    private ?string $bank = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cardNum = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $accountNum = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shabaNum = null;

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

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): static
    {
        $this->person = $person;

        return $this;
    }

    public function getBank(): ?string
    {
        return $this->bank;
    }

    public function setBank(string $bank): static
    {
        $this->bank = $bank;

        return $this;
    }

    public function getCardNum(): ?string
    {
        return $this->cardNum;
    }

    public function setCardNum(?string $cardNum): static
    {
        $this->cardNum = $cardNum;

        return $this;
    }

    public function getAccountNum(): ?string
    {
        return $this->accountNum;
    }

    public function setAccountNum(?string $accountNum): static
    {
        $this->accountNum = $accountNum;

        return $this;
    }

    public function getShabaNum(): ?string
    {
        return $this->shabaNum;
    }

    public function setShabaNum(?string $shabaNum): static
    {
        $this->shabaNum = $shabaNum;

        return $this;
    }
}
