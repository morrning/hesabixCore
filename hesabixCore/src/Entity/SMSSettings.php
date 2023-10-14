<?php

namespace App\Entity;

use App\Repository\SMSSettingsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SMSSettingsRepository::class)]
class SMSSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'sMSSettings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Business $bid = null;

    #[ORM\Column(nullable: true)]
    private ?bool $sendAfterSell = false;

    #[ORM\Column(nullable: true)]
    private ?bool $sendAfterSellPayOnline = false;

    #[ORM\Column(nullable: true)]
    private ?bool $sendAfterBuy = false;

    #[ORM\Column(nullable: true)]
    private ?bool $sendAfterBuyToUser = false;

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

    public function isSendAfterSell(): ?bool
    {
        return $this->sendAfterSell;
    }

    public function setSendAfterSell(?bool $sendAfterSell): static
    {
        $this->sendAfterSell = $sendAfterSell;

        return $this;
    }

    public function isSendAfterSellPayOnline(): ?bool
    {
        return $this->sendAfterSellPayOnline;
    }

    public function setSendAfterSellPayOnline(?bool $sendAfterSellPayOnline): static
    {
        $this->sendAfterSellPayOnline = $sendAfterSellPayOnline;

        return $this;
    }

    public function isSendAfterBuy(): ?bool
    {
        return $this->sendAfterBuy;
    }

    public function setSendAfterBuy(?bool $sendAfterBuy): static
    {
        $this->sendAfterBuy = $sendAfterBuy;

        return $this;
    }

    public function isSendAfterBuyToUser(): ?bool
    {
        return $this->sendAfterBuyToUser;
    }

    public function setSendAfterBuyToUser(?bool $sendAfterBuyToUser): static
    {
        $this->sendAfterBuyToUser = $sendAfterBuyToUser;

        return $this;
    }
}
