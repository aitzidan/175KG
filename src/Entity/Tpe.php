<?php

namespace App\Entity;

use App\Repository\TpeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TpeRepository::class)]
class Tpe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    private ?float $tpe_caisse = null;

    #[ORM\Column]
    private ?float $tpe_naps = null;

    #[ORM\Column]
    private ?float $ecart = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_creation = null;

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

    public function getTpeCaisse(): ?float
    {
        return $this->tpe_caisse;
    }

    public function setTpeCaisse(float $tpe_caisse): static
    {
        $this->tpe_caisse = $tpe_caisse;

        return $this;
    }

    public function getTpeNaps(): ?float
    {
        return $this->tpe_naps;
    }

    public function setTpeNaps(float $tpe_naps): static
    {
        $this->tpe_naps = $tpe_naps;

        return $this;
    }

    public function getEcart(): ?float
    {
        return $this->ecart;
    }

    public function setEcart(float $ecart): static
    {
        $this->ecart = $ecart;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): static
    {
        $this->date_creation = $date_creation;

        return $this;
    }
}
