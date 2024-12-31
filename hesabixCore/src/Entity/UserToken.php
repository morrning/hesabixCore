<?php

namespace App\Entity;

use App\Repository\UserTokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserTokenRepository::class)]
class UserToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $token = null;

    #[ORM\ManyToOne(inversedBy: 'userTokens')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tokenID = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $lastActive = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getTokenID(): ?string
    {
        return $this->tokenID;
    }

    public function setTokenID(?string $tokenID): static
    {
        $this->tokenID = $tokenID;

        return $this;
    }

    public function getLastActive(): ?string
    {
        return $this->lastActive;
    }

    public function setLastActive(?string $lastActive): static
    {
        $this->lastActive = $lastActive;

        return $this;
    }
}
