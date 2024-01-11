<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = ['ROLE_USER'];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserToken::class, orphanRemoval: true)]
    private Collection $userTokens;

    #[ORM\Column(length: 255)]
    private ?string $fullName = null;

    #[ORM\Column(length: 50)]
    private ?string $dateRegister = null;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Business::class, orphanRemoval: true)]
    private Collection $businesses;

    #[ORM\OneToMany(mappedBy: 'submiter', targetEntity: GuideContent::class, orphanRemoval: true)]
    private Collection $guideContents;

    #[ORM\OneToMany(mappedBy: 'submitter', targetEntity: StackContent::class, orphanRemoval: true)]
    private Collection $stackContents;

    #[ORM\OneToMany(mappedBy: 'submitter', targetEntity: BlogPost::class, orphanRemoval: true)]
    private Collection $blogPosts;

    #[ORM\OneToMany(mappedBy: 'submitter', targetEntity: BlogComment::class, orphanRemoval: true)]
    private Collection $blogComments;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Log::class)]
    private Collection $logs;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Permission::class)]
    private Collection $permissions;

    #[ORM\OneToMany(mappedBy: 'submitter', targetEntity: HesabdariDoc::class, orphanRemoval: true)]
    private Collection $hesabdariDocs;

    #[ORM\OneToMany(mappedBy: 'submitter', targetEntity: Support::class, orphanRemoval: true)]
    private Collection $supports;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Notification::class, orphanRemoval: true)]
    private Collection $notifications;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $mobile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $verifyCode = null;

    #[ORM\Column(nullable: true)]
    private ?bool $active = null;

    #[ORM\OneToMany(mappedBy: 'submitter', targetEntity: EmailHistory::class)]
    private Collection $emailHistories;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $verifyCodeTime = null;

    #[ORM\OneToMany(mappedBy: 'submitter', targetEntity: SMSPays::class, orphanRemoval: true)]
    private Collection $sMSPays;

    #[ORM\OneToMany(mappedBy: 'submitter', targetEntity: WalletTransaction::class)]
    private Collection $walletTransactions;

    #[ORM\OneToMany(mappedBy: 'submitter', targetEntity: StoreroomTicket::class, orphanRemoval: true)]
    private Collection $storeroomTickets;

    #[ORM\OneToMany(mappedBy: 'Submitter', targetEntity: ArchiveFile::class, orphanRemoval: true)]
    private Collection $archiveFiles;

    #[ORM\OneToMany(mappedBy: 'submitter', targetEntity: ArchiveOrders::class, orphanRemoval: true)]
    private Collection $archiveOrders;

    #[ORM\OneToMany(mappedBy: 'submitter', targetEntity: Hook::class, orphanRemoval: true)]
    private Collection $hooks;

    public function __construct()
    {
        $this->userTokens = new ArrayCollection();
        $this->businesses = new ArrayCollection();
        $this->guideContents = new ArrayCollection();
        $this->stackContents = new ArrayCollection();
        $this->blogPosts = new ArrayCollection();
        $this->blogComments = new ArrayCollection();
        $this->logs = new ArrayCollection();
        $this->permissions = new ArrayCollection();
        $this->hesabdariDocs = new ArrayCollection();
        $this->supports = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->emailHistories = new ArrayCollection();
        $this->sMSPays = new ArrayCollection();
        $this->walletTransactions = new ArrayCollection();
        $this->storeroomTickets = new ArrayCollection();
        $this->archiveFiles = new ArrayCollection();
        $this->archiveOrders = new ArrayCollection();
        $this->hooks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, UserToken>
     */
    public function getUserTokens(): Collection
    {
        return $this->userTokens;
    }

    public function addUserToken(UserToken $userToken): self
    {
        if (!$this->userTokens->contains($userToken)) {
            $this->userTokens->add($userToken);
            $userToken->setUser($this);
        }

        return $this;
    }

    public function removeUserToken(UserToken $userToken): self
    {
        if ($this->userTokens->removeElement($userToken)) {
            // set the owning side to null (unless already changed)
            if ($userToken->getUser() === $this) {
                $userToken->setUser(null);
            }
        }

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getDateRegister(): ?string
    {
        return $this->dateRegister;
    }

    public function setDateRegister(string $dateRegister): self
    {
        $this->dateRegister = $dateRegister;

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
            $business->setOwner($this);
        }

        return $this;
    }

    public function removeBusiness(Business $business): self
    {
        if ($this->businesses->removeElement($business)) {
            // set the owning side to null (unless already changed)
            if ($business->getOwner() === $this) {
                $business->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, GuideContent>
     */
    public function getGuideContents(): Collection
    {
        return $this->guideContents;
    }

    public function addGuideContent(GuideContent $guideContent): self
    {
        if (!$this->guideContents->contains($guideContent)) {
            $this->guideContents->add($guideContent);
            $guideContent->setSubmiter($this);
        }

        return $this;
    }

    public function removeGuideContent(GuideContent $guideContent): self
    {
        if ($this->guideContents->removeElement($guideContent)) {
            // set the owning side to null (unless already changed)
            if ($guideContent->getSubmiter() === $this) {
                $guideContent->setSubmiter(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, StackContent>
     */
    public function getStackContents(): Collection
    {
        return $this->stackContents;
    }

    public function addStackContent(StackContent $stackContent): self
    {
        if (!$this->stackContents->contains($stackContent)) {
            $this->stackContents->add($stackContent);
            $stackContent->setSubmitter($this);
        }

        return $this;
    }

    public function removeStackContent(StackContent $stackContent): self
    {
        if ($this->stackContents->removeElement($stackContent)) {
            // set the owning side to null (unless already changed)
            if ($stackContent->getSubmitter() === $this) {
                $stackContent->setSubmitter(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BlogPost>
     */
    public function getBlogPosts(): Collection
    {
        return $this->blogPosts;
    }

    public function addBlogPost(BlogPost $blogPost): self
    {
        if (!$this->blogPosts->contains($blogPost)) {
            $this->blogPosts->add($blogPost);
            $blogPost->setSubmitter($this);
        }

        return $this;
    }

    public function removeBlogPost(BlogPost $blogPost): self
    {
        if ($this->blogPosts->removeElement($blogPost)) {
            // set the owning side to null (unless already changed)
            if ($blogPost->getSubmitter() === $this) {
                $blogPost->setSubmitter(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BlogComment>
     */
    public function getBlogComments(): Collection
    {
        return $this->blogComments;
    }

    public function addBlogComment(BlogComment $blogComment): self
    {
        if (!$this->blogComments->contains($blogComment)) {
            $this->blogComments->add($blogComment);
            $blogComment->setSubmitter($this);
        }

        return $this;
    }

    public function removeBlogComment(BlogComment $blogComment): self
    {
        if ($this->blogComments->removeElement($blogComment)) {
            // set the owning side to null (unless already changed)
            if ($blogComment->getSubmitter() === $this) {
                $blogComment->setSubmitter(null);
            }
        }

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
            $log->setUser($this);
        }

        return $this;
    }

    public function removeLog(Log $log): self
    {
        if ($this->logs->removeElement($log)) {
            // set the owning side to null (unless already changed)
            if ($log->getUser() === $this) {
                $log->setUser(null);
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
            $permission->setUser($this);
        }

        return $this;
    }

    public function removePermission(Permission $permission): self
    {
        if ($this->permissions->removeElement($permission)) {
            // set the owning side to null (unless already changed)
            if ($permission->getUser() === $this) {
                $permission->setUser(null);
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

    public function addHesabdariDoc(HesabdariDoc $hesabdariDoc): self
    {
        if (!$this->hesabdariDocs->contains($hesabdariDoc)) {
            $this->hesabdariDocs->add($hesabdariDoc);
            $hesabdariDoc->setSubmitter($this);
        }

        return $this;
    }

    public function removeHesabdariDoc(HesabdariDoc $hesabdariDoc): self
    {
        if ($this->hesabdariDocs->removeElement($hesabdariDoc)) {
            // set the owning side to null (unless already changed)
            if ($hesabdariDoc->getSubmitter() === $this) {
                $hesabdariDoc->setSubmitter(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Support>
     */
    public function getSupports(): Collection
    {
        return $this->supports;
    }

    public function addSupport(Support $support): self
    {
        if (!$this->supports->contains($support)) {
            $this->supports->add($support);
            $support->setSubmitter($this);
        }

        return $this;
    }

    public function removeSupport(Support $support): self
    {
        if ($this->supports->removeElement($support)) {
            // set the owning side to null (unless already changed)
            if ($support->getSubmitter() === $this) {
                $support->setSubmitter(null);
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
            $notification->setUser($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getUser() === $this) {
                $notification->setUser(null);
            }
        }

        return $this;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(?string $mobile): static
    {
        $this->mobile = $mobile;

        return $this;
    }

    public function getVerifyCode(): ?string
    {
        return $this->verifyCode;
    }

    public function setVerifyCode(?string $verifyCode): static
    {
        $this->verifyCode = $verifyCode;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Collection<int, EmailHistory>
     */
    public function getEmailHistories(): Collection
    {
        return $this->emailHistories;
    }

    public function addEmailHistory(EmailHistory $emailHistory): static
    {
        if (!$this->emailHistories->contains($emailHistory)) {
            $this->emailHistories->add($emailHistory);
            $emailHistory->setSubmitter($this);
        }

        return $this;
    }

    public function removeEmailHistory(EmailHistory $emailHistory): static
    {
        if ($this->emailHistories->removeElement($emailHistory)) {
            // set the owning side to null (unless already changed)
            if ($emailHistory->getSubmitter() === $this) {
                $emailHistory->setSubmitter(null);
            }
        }

        return $this;
    }

    public function getVerifyCodeTime(): ?string
    {
        return $this->verifyCodeTime;
    }

    public function setVerifyCodeTime(?string $verifyCodeTime): static
    {
        $this->verifyCodeTime = $verifyCodeTime;

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
            $sMSPay->setSubmitter($this);
        }

        return $this;
    }

    public function removeSMSPay(SMSPays $sMSPay): static
    {
        if ($this->sMSPays->removeElement($sMSPay)) {
            // set the owning side to null (unless already changed)
            if ($sMSPay->getSubmitter() === $this) {
                $sMSPay->setSubmitter(null);
            }
        }

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
            $walletTransaction->setSubmitter($this);
        }

        return $this;
    }

    public function removeWalletTransaction(WalletTransaction $walletTransaction): static
    {
        if ($this->walletTransactions->removeElement($walletTransaction)) {
            // set the owning side to null (unless already changed)
            if ($walletTransaction->getSubmitter() === $this) {
                $walletTransaction->setSubmitter(null);
            }
        }

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
            $storeroomTicket->setSubmitter($this);
        }

        return $this;
    }

    public function removeStoreroomTicket(StoreroomTicket $storeroomTicket): static
    {
        if ($this->storeroomTickets->removeElement($storeroomTicket)) {
            // set the owning side to null (unless already changed)
            if ($storeroomTicket->getSubmitter() === $this) {
                $storeroomTicket->setSubmitter(null);
            }
        }

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
            $archiveFile->setSubmitter($this);
        }

        return $this;
    }

    public function removeArchiveFile(ArchiveFile $archiveFile): static
    {
        if ($this->archiveFiles->removeElement($archiveFile)) {
            // set the owning side to null (unless already changed)
            if ($archiveFile->getSubmitter() === $this) {
                $archiveFile->setSubmitter(null);
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
            $archiveOrder->setSubmitter($this);
        }

        return $this;
    }

    public function removeArchiveOrder(ArchiveOrders $archiveOrder): static
    {
        if ($this->archiveOrders->removeElement($archiveOrder)) {
            // set the owning side to null (unless already changed)
            if ($archiveOrder->getSubmitter() === $this) {
                $archiveOrder->setSubmitter(null);
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
            $hook->setSubmitter($this);
        }

        return $this;
    }

    public function removeHook(Hook $hook): static
    {
        if ($this->hooks->removeElement($hook)) {
            // set the owning side to null (unless already changed)
            if ($hook->getSubmitter() === $this) {
                $hook->setSubmitter(null);
            }
        }

        return $this;
    }
}
