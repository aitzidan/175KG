<?php

namespace App\Entity;

use App\Repository\ReglementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: ReglementRepository::class)]
class Reglement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $numero = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Fournisseur $destinataire = null;

    #[ORM\Column(length: 255)]
    private ?string $numeroFacture = null;

    #[ORM\Column(length: 255)]
    private ?string $details = null;

    #[ORM\Column]
    private ?float $montant = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_encaissement = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entity $idEntity = null;

    #[ORM\Column(length: 255)]
    private ?string $banque = null;

    #[ORM\Column(nullable: true)]
    private ?int $etat = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(string $numero): static
    {
        $this->numero = $numero;

        return $this;
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

    public function getDestinataire(): ?Fournisseur
    {
        return $this->destinataire;
    }

    public function setDestinataire(?Fournisseur $destinataire): static
    {
        $this->destinataire = $destinataire;

        return $this;
    }

    public function getNumeroFacture(): ?string
    {
        return $this->numeroFacture;
    }

    public function setNumeroFacture(string $numeroFacture): static
    {
        $this->numeroFacture = $numeroFacture;

        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(string $details): static
    {
        $this->details = $details;

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

    public function getDateEncaissement(): ?\DateTimeInterface
    {
        return $this->date_encaissement;
    }

    public function setDateEncaissement(\DateTimeInterface $date_encaissement): static
    {
        $this->date_encaissement = $date_encaissement;

        return $this;
    }

    public function getIdEntity(): ?entity
    {
        return $this->idEntity;
    }

    public function setIdEntity(?entity $idEntity): static
    {
        $this->idEntity = $idEntity;

        return $this;
    }

    public function getBanque(): ?string
    {
        return $this->banque;
    }

    public function setBanque(string $banque): static
    {
        $this->banque = $banque;

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
