<?php

namespace App\Entity;

use App\Repository\DetailBalanceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DetailBalanceRepository::class)]
class DetailBalance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $section = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $operations = null;

    #[ORM\Column(nullable: true)]
    private ?float $poids = null;

    #[ORM\Column]
    private ?float $montant = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->Nom;
    }

    public function setNom(string $Nom): static
    {
        $this->Nom = $Nom;

        return $this;
    }

    public function getSection(): ?string
    {
        return $this->section;
    }

    public function setSection(?string $section): static
    {
        $this->section = $section;

        return $this;
    }

    public function getOperations(): ?string
    {
        return $this->operations;
    }

    public function setOperations(?string $operations): static
    {
        $this->operations = $operations;

        return $this;
    }

    public function getPoids(): ?float
    {
        return $this->poids;
    }

    public function setPoids(?float $poids): static
    {
        $this->poids = $poids;

        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): static
    {
        $this->montant = $montant;

        return $this;
    }
}
