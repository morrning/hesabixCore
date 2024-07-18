<?php

namespace App\Entity;

use App\Repository\PriceListDetailRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PriceListDetailRepository::class)]
class PriceListDetail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'priceListDetails')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PriceList $list = null;

    #[ORM\ManyToOne(inversedBy: 'priceListDetails')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Commodity $commodity = null;

    #[ORM\ManyToOne(inversedBy: 'priceListDetails')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Money $money = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $priceBuy = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $priceSell = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getList(): ?PriceList
    {
        return $this->list;
    }

    public function setList(?PriceList $list): static
    {
        $this->list = $list;

        return $this;
    }

    public function getCommodity(): ?Commodity
    {
        return $this->commodity;
    }

    public function setCommodity(?Commodity $commodity): static
    {
        $this->commodity = $commodity;

        return $this;
    }

    public function getMoney(): ?Money
    {
        return $this->money;
    }

    public function setMoney(?Money $money): static
    {
        $this->money = $money;

        return $this;
    }

    public function getPriceBuy(): ?string
    {
        return $this->priceBuy;
    }

    public function setPriceBuy(?string $priceBuy): static
    {
        $this->priceBuy = $priceBuy;

        return $this;
    }

    public function getPriceSell(): ?string
    {
        return $this->priceSell;
    }

    public function setPriceSell(?string $priceSell): static
    {
        $this->priceSell = $priceSell;

        return $this;
    }
}
