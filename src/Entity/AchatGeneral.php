<?php

namespace App\Entity;

use App\Repository\AchatGeneralRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AchatGeneralRepository::class)]
class AchatGeneral
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Categorie $categorie = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Fournisseur $id_fournisseur = null;

    #[ORM\Column(length: 255)]
    private ?string $unite = null;

    #[ORM\Column(nullable: true)]
    private ?float $qte = null;

    #[ORM\Column]
    private ?float $prix = null;

    #[ORM\Column(nullable: true)]
    private ?float $montant = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Designation $idDesignation = null;

    #[ORM\Column(nullable: true)]
    private ?int $etat = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getCategorie(): ?categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?categorie $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getIdFournisseur(): ?fournisseur
    {
        return $this->id_fournisseur;
    }

    public function setIdFournisseur(?fournisseur $id_fournisseur): static
    {
        $this->id_fournisseur = $id_fournisseur;

        return $this;
    }

    public function getUnite(): ?string
    {
        return $this->unite;
    }

    public function setUnite(string $unite): static
    {
        $this->unite = $unite;

        return $this;
    }

    public function getQte(): ?float
    {
        return $this->qte;
    }

    public function setQte(?float $qte): static
    {
        $this->qte = $qte;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(?float $montant): static
    {
        $this->montant = $montant;

        return $this;
    }

    public function getIdDesignation(): ?Designation
    {
        return $this->idDesignation;
    }

    public function setIdDesignation(?Designation $idDesignation): static
    {
        $this->idDesignation = $idDesignation;

        return $this;
    }

    public function getEtat(): ?int
    {
        return $this->etat;
    }

    public function setEtat(?int $etat): static
    {
        $this->etat = $etat;

        return $this;
    }
}
