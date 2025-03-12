<?php

namespace App\Entity;

use App\Repository\SettingsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SettingsRepository::class)]
class Settings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(nullable: true)]
    private ?bool $activeSendSms = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $appSite = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $siteKeywords = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $discription = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $scripts = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $footerScripts = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $footer = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isActiveSendSms(): ?bool
    {
        return $this->activeSendSms;
    }

    public function setActiveSendSms(?bool $activeSendSms): static
    {
        $this->activeSendSms = $activeSendSms;

        return $this;
    }

    public function getAppSite(): ?string
    {
        return $this->appSite;
    }

    public function setAppSite(?string $appSite): static
    {
        $this->appSite = $appSite;

        return $this;
    }

    public function getSiteKeywords(): ?string
    {
        return $this->siteKeywords;
    }

    public function setSiteKeywords(?string $siteKeywords): static
    {
        $this->siteKeywords = $siteKeywords;

        return $this;
    }

    public function getDiscription(): ?string
    {
        return $this->discription;
    }

    public function setDiscription(?string $discription): static
    {
        $this->discription = $discription;

        return $this;
    }

    public function getScripts(): ?string
    {
        return $this->scripts;
    }

    public function setScripts(?string $scripts): static
    {
        $this->scripts = $scripts;

        return $this;
    }

    public function getFooterScripts(): ?string
    {
        return $this->footerScripts;
    }

    public function setFooterScripts(?string $footerScripts): static
    {
        $this->footerScripts = $footerScripts;

        return $this;
    }

    public function getFooter(): ?string
    {
        return $this->footer;
    }

    public function setFooter(?string $footer): static
    {
        $this->footer = $footer;

        return $this;
    }
}
