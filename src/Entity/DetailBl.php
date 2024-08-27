<?php

namespace App\Entity;

use App\Repository\DetailBlRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DetailBlRepository::class)]
class DetailBl
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Bl $idBl = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Produit $idProduit = null;

    #[ORM\Column(length: 255)]
    private ?string $qte = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdBl(): ?bl
    {
        return $this->idBl;
    }

    public function setIdBl(?bl $idBl): static
    {
        $this->idBl = $idBl;

        return $this;
    }

    public function getIdProduit(): ?Produit
    {
        return $this->idProduit;
    }

    public function setIdProduit(?Produit $idProduit): static
    {
        $this->idProduit = $idProduit;

        return $this;
    }

    public function getQte(): ?string
    {
        return $this->qte;
    }

    public function setQte(string $qte): static
    {
        $this->qte = $qte;

        return $this;
    }
}
