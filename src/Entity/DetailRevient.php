<?php

namespace App\Entity;

use App\Repository\DetailRevientRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DetailRevientRepository::class)]
class DetailRevient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Revient $idRevient = null;

    #[ORM\Column(length: 255)]
    private ?string $designation = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Fournisseur $fournisseur = null;

    #[ORM\Column(length: 255)]
    private ?string $unite = null;

    #[ORM\Column]
    private ?float $cout_achat = null;

    #[ORM\Column]
    private ?float $unite_necessaire = null;

    #[ORM\Column]
    private ?float $prix_ht = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdRevient(): ?revient
    {
        return $this->idRevient;
    }

    public function setIdRevient(?revient $idRevient): static
    {
        $this->idRevient = $idRevient;

        return $this;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(string $designation): static
    {
        $this->designation = $designation;

        return $this;
    }

    public function getFournisseur(): ?Fournisseur
    {
        return $this->fournisseur;
    }

    public function setFournisseur(?Fournisseur $fournisseur): static
    {
        $this->fournisseur = $fournisseur;

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

    public function getCoutAchat(): ?float
    {
        return $this->cout_achat;
    }

    public function setCoutAchat(float $cout_achat): static
    {
        $this->cout_achat = $cout_achat;

        return $this;
    }

    public function getUniteNecessaire(): ?float
    {
        return $this->unite_necessaire;
    }

    public function setUniteNecessaire(float $unite_necessaire): static
    {
        $this->unite_necessaire = $unite_necessaire;

        return $this;
    }

    public function getPrixHt(): ?float
    {
        return $this->prix_ht;
    }

    public function setPrixHt(float $prix_ht): static
    {
        $this->prix_ht = $prix_ht;

        return $this;
    }
}
