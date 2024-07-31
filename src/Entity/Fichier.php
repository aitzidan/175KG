<?php

namespace App\Entity;

use App\Repository\FichierRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FichierRepository::class)]
class Fichier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $url = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?TypeFichier $type = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Analyse $id_analyse = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getType(): ?TypeFichier
    {
        return $this->type;
    }

    public function setType(?TypeFichier $type): static
    {
        $this->type = $type;

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
}
