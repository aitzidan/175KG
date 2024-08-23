<?php

namespace App\Entity;

use App\Repository\CaisseMagasinRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CaisseMagasinRepository::class)]
class CaisseMagasin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    private ?float $tpe = null;

    #[ORM\Column]
    private ?float $espece = null;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_creation = null;

    #[ORM\Column]
    private ?float $espece_final = null;

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

    public function getTpe(): ?float
    {
        return $this->tpe;
    }

    public function setTpe(float $tpe): static
    {
        $this->tpe = $tpe;

        return $this;
    }

    public function getEspece(): ?float
    {
        return $this->espece;
    }

    public function setEspece(float $espece): static
    {
        $this->espece = $espece;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

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

    public function getEspeceFinal(): ?float
    {
        return $this->espece_final;
    }

    public function setEspeceFinal(float $espece_final): static
    {
        $this->espece_final = $espece_final;

        return $this;
    }
}
