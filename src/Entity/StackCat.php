<?php

namespace App\Entity;

use App\Repository\StackCatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StackCatRepository::class)]
class StackCat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $code = null;

    #[ORM\OneToMany(mappedBy: 'cat', targetEntity: StackContent::class, orphanRemoval: true)]
    private Collection $stackContents;

    public function __construct()
    {
        $this->stackContents = new ArrayCollection();
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
            $stackContent->setCat($this);
        }

        return $this;
    }

    public function removeStackContent(StackContent $stackContent): self
    {
        if ($this->stackContents->removeElement($stackContent)) {
            // set the owning side to null (unless already changed)
            if ($stackContent->getCat() === $this) {
                $stackContent->setCat(null);
            }
        }

        return $this;
    }
}
