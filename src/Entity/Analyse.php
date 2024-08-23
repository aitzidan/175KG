<?php

namespace App\Entity;

use App\Repository\AnalyseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnalyseRepository::class)]
class Analyse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_creation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_debut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_fin = null;

    #[ORM\Column(nullable: true)]
    private ?float $prixCaisseInf = null;

    #[ORM\Column(nullable: true)]
    private ?float $prixCaisseSup = null;

    #[ORM\Column(nullable: true)]
    private ?float $poidsCaisseInf = null;

    #[ORM\Column(nullable: true)]
    private ?float $poidsCaisseSup = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(?\DateTimeInterface $date_creation): static
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->date_debut;
    }

    public function setDateDebut(\DateTimeInterface $date_debut): static
    {
        $this->date_debut = $date_debut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->date_fin;
    }

    public function setDateFin(\DateTimeInterface $date_fin): static
    {
        $this->date_fin = $date_fin;

        return $this;
    }

    public function getPrixCaisseInf(): ?float
    {
        return $this->prixCaisseInf;
    }

    public function setPrixCaisseInf(?float $prixCaisseInf): static
    {
        $this->prixCaisseInf = $prixCaisseInf;

        return $this;
    }

    public function getPrixCaisseSup(): ?float
    {
        return $this->prixCaisseSup;
    }

    public function setPrixCaisseSup(?float $prixCaisseSup): static
    {
        $this->prixCaisseSup = $prixCaisseSup;

        return $this;
    }

    public function getPoidsCaisseInf(): ?float
    {
        return $this->poidsCaisseInf;
    }

    public function setPoidsCaisseInf(?float $poidsCaisseInf): static
    {
        $this->poidsCaisseInf = $poidsCaisseInf;

        return $this;
    }

    public function getPoidsCaisseSup(): ?float
    {
        return $this->poidsCaisseSup;
    }

    public function setPoidsCaisseSup(?float $poidsCaisseSup): static
    {
        $this->poidsCaisseSup = $poidsCaisseSup;

        return $this;
    }
}
