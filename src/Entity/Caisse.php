<?php

namespace App\Entity;

use App\Repository\CaisseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CaisseRepository::class)]
class Caisse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $caisse = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCaisse(): ?string
    {
        return $this->caisse;
    }

    public function setCaisse(string $caisse): static
    {
        $this->caisse = $caisse;

        return $this;
    }

}
