<?php

namespace App\Entity;

use App\Repository\InvoiceTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceTypeRepository::class)]
class InvoiceType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\Column(length: 255)]
    private ?string $code = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $checked = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\OneToMany(mappedBy: 'InvoiceLabel', targetEntity: HesabdariDoc::class)]
    private Collection $hesabdariDocs;

    /**
     * @var Collection<int, PreInvoiceDoc>
     */
    #[ORM\OneToMany(mappedBy: 'invoiceLabel', targetEntity: PreInvoiceDoc::class)]
    private Collection $preInvoiceDocs;

    public function __construct()
    {
        $this->hesabdariDocs = new ArrayCollection();
        $this->preInvoiceDocs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getChecked(): ?string
    {
        return $this->checked;
    }

    public function setChecked(?string $checked): static
    {
        $this->checked = $checked;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection<int, HesabdariDoc>
     */
    public function getHesabdariDocs(): Collection
    {
        return $this->hesabdariDocs;
    }

    public function addHesabdariDoc(HesabdariDoc $hesabdariDoc): static
    {
        if (!$this->hesabdariDocs->contains($hesabdariDoc)) {
            $this->hesabdariDocs->add($hesabdariDoc);
            $hesabdariDoc->setInvoiceLabel($this);
        }

        return $this;
    }

    public function removeHesabdariDoc(HesabdariDoc $hesabdariDoc): static
    {
        if ($this->hesabdariDocs->removeElement($hesabdariDoc)) {
            // set the owning side to null (unless already changed)
            if ($hesabdariDoc->getInvoiceLabel() === $this) {
                $hesabdariDoc->setInvoiceLabel(null);
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
            $preInvoiceDoc->setInvoiceLabel($this);
        }

        return $this;
    }

    public function removePreInvoiceDoc(PreInvoiceDoc $preInvoiceDoc): static
    {
        if ($this->preInvoiceDocs->removeElement($preInvoiceDoc)) {
            // set the owning side to null (unless already changed)
            if ($preInvoiceDoc->getInvoiceLabel() === $this) {
                $preInvoiceDoc->setInvoiceLabel(null);
            }
        }

        return $this;
    }
}
