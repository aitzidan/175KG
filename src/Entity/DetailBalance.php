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

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Analyse $id_analyse = null;

    #[ORM\Column(length: 255)]
    private ?string $log_action = null;


    #[ORM\Column(nullable: true)]
    private ?int $etatPoids = null;

    #[ORM\Column(nullable: true)]
    private ?int $etatPrix = null;

    #[ORM\Column(nullable: true)]
    private ?int $code = null;

    #[ORM\Column(nullable: true)]
    private ?float $ecart_poids = null;

    #[ORM\Column(nullable: true)]
    private ?float $ecart_prix = null;

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

    public function getIdAnalyse(): ?Analyse
    {
        return $this->id_analyse;
    }

    public function setIdAnalyse(?Analyse $id_analyse): static
    {
        $this->id_analyse = $id_analyse;

        return $this;
    }

    public function getLogAction(): ?string
    {
        return $this->log_action;
    }

    public function setLogAction(string $log_action): static
    {
        $this->log_action = $log_action;

        return $this;
    }

    public function getEtatPoids(): ?int
    {
        return $this->etatPoids;
    }

    public function setEtatPoids(?int $etatPoids): static
    {
        $this->etatPoids = $etatPoids;

        return $this;
    }

    public function getEtatPrix(): ?int
    {
        return $this->etatPrix;
    }

    public function setEtatPrix(?int $etatPrix): static
    {
        $this->etatPrix = $etatPrix;

        return $this;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function setCode(?int $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getEcartPoids(): ?float
    {
        return $this->ecart_poids;
    }

    public function setEcartPoids(?float $ecart_poids): static
    {
        $this->ecart_poids = $ecart_poids !== null ? round($ecart_poids, 2) : null;

        return $this;
    }

    public function getEcartPrix(): ?float
    {
        return $this->ecart_prix;
    }

    public function setEcartPrix(?float $ecart_prix): static
    {
        $this->ecart_prix = $ecart_prix !== null ? round($ecart_prix, 2) : null;
        return $this;
    }
}
