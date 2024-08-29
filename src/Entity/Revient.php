<?php

namespace App\Entity;

use App\Repository\RevientRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RevientRepository::class)]
class Revient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom_produit = null;

    #[ORM\Column]
    private ?float $nombre_unite = null;

    #[ORM\Column]
    private ?float $total_ht = null;

    #[ORM\Column]
    private ?float $prix_ht = null;

    #[ORM\Column]
    private ?float $prix_vente_ht = null;

    #[ORM\Column]
    private ?float $marge_brute = null;

    #[ORM\Column]
    private ?float $taux_marge = null;

    #[ORM\Column]
    private ?float $coefficient_marge = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomProduit(): ?string
    {
        return $this->nom_produit;
    }

    public function setNomProduit(string $nom_produit): static
    {
        $this->nom_produit = $nom_produit;

        return $this;
    }

    public function getNombreUnite(): ?float
    {
        return $this->nombre_unite;
    }

    public function setNombreUnite(float $nombre_unite): static
    {
        $this->nombre_unite = $nombre_unite;

        return $this;
    }

    public function getTotalHt(): ?float
    {
        return $this->total_ht;
    }

    public function setTotalHt(float $total_ht): static
    {
        $this->total_ht = $total_ht;

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

    public function getPrixVenteHt(): ?float
    {
        return $this->prix_vente_ht;
    }

    public function setPrixVenteHt(float $prix_vente_ht): static
    {
        $this->prix_vente_ht = $prix_vente_ht;

        return $this;
    }

    public function getMargeBrute(): ?float
    {
        return $this->marge_brute;
    }

    public function setMargeBrute(float $marge_brute): static
    {
        $this->marge_brute = $marge_brute;

        return $this;
    }

    public function getTauxMarge(): ?float
    {
        return $this->taux_marge;
    }

    public function setTauxMarge(float $taux_marge): static
    {
        $this->taux_marge = $taux_marge;

        return $this;
    }

    public function getCoefficientMarge(): ?float
    {
        return $this->coefficient_marge;
    }

    public function setCoefficientMarge(float $coefficient_marge): static
    {
        $this->coefficient_marge = $coefficient_marge;

        return $this;
    }
}
