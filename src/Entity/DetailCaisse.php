<?php

namespace App\Entity;

use App\Repository\DetailCaisseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DetailCaisseRepository::class)]
class DetailCaisse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $ticket = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $dates = null;

    #[ORM\Column(type: 'time')]
    private ?\DateTimeInterface $heure = null;

    #[ORM\Column(length: 255)]
    private ?string $code = null;
    #[ORM\Column(length: 255)]
    private ?string $article = null;

    #[ORM\Column(length: 255)]
    private ?string $famille = null;

    #[ORM\Column(type: 'float')]
    private ?float $prixA = null;

    #[ORM\Column(type: 'float')]
    private ?float $prixMP = null;

    #[ORM\Column(type: 'float')]
    private ?float $prixV = null;

    #[ORM\Column(type: 'float')]
    private ?int $qte = null;

    #[ORM\Column(type: 'float')]
    private ?float $remise = null;

    #[ORM\Column(type: 'float')]
    private ?float $totalNet = null;

    #[ORM\Column(length: 255)]
    private ?string $caissier = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $vendeur = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $codeClient = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $client = null;

    #[ORM\Column(type: 'integer')]
    private ?int $poste = null;

    #[ORM\Column(length: 255)]
    private ?string $operation = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $clotureCaissier = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $clotureGlobale = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Analyse $id_analyse = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $log_action = null;


    public function getId(): ?int
    {
        return $this->id;
    }
    public function getTicket(): ?string
    {
        return $this->ticket;
    }

    public function setTicket(string $ticket): static
    {
        $this->ticket = $ticket;

        return $this;
    }

    public function getDates(): ?\DateTimeInterface
    {
        return $this->dates;
    }

    public function setDates(\DateTimeInterface $dates): static
    {
        $this->dates = $dates;

        return $this;
    }

    public function getHeure(): ?\DateTimeInterface
    {
        return $this->heure;
    }

    public function setHeure(\DateTimeInterface $heure): static
    {
        $this->heure = $heure;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getArticle(): ?string
    {
        return $this->article;
    }

    public function setArticle(string $article): static
    {
        $this->article = $article;

        return $this;
    }

    public function getFamille(): ?string
    {
        return $this->famille;
    }

    public function setFamille(string $famille): static
    {
        $this->famille = $famille;

        return $this;
    }

    public function getPrixA(): ?float
    {
        return $this->prixA;
    }

    public function setPrixA(float $prixA): static
    {
        $this->prixA = $prixA;

        return $this;
    }

    public function getPrixMP(): ?float
    {
        return $this->prixMP;
    }

    public function setPrixMP(float $prixMP): static
    {
        $this->prixMP = $prixMP;

        return $this;
    }

    public function getPrixV(): ?float
    {
        return $this->prixV;
    }

    public function setPrixV(float $prixV): static
    {
        $this->prixV = $prixV;

        return $this;
    }

    public function getQte(): ?float
    {
        return $this->qte;
    }

    public function setQte(float $qte): static
    {
        $this->qte = $qte;

        return $this;
    }

    public function getRemise(): ?float
    {
        return $this->remise;
    }

    public function setRemise(float $remise): static
    {
        $this->remise = $remise;

        return $this;
    }

    public function getTotalNet(): ?float
    {
        return $this->totalNet;
    }

    public function setTotalNet(float $totalNet): static
    {
        $this->totalNet = $totalNet;

        return $this;
    }

    public function getCaissier(): ?string
    {
        return $this->caissier;
    }

    public function setCaissier(string $caissier): static
    {
        $this->caissier = $caissier;

        return $this;
    }

    public function getVendeur(): ?string
    {
        return $this->vendeur;
    }

    public function setVendeur(?string $vendeur): static
    {
        $this->vendeur = $vendeur;

        return $this;
    }

    public function getCodeClient(): ?string
    {
        return $this->codeClient;
    }

    public function setCodeClient(?string $codeClient): static
    {
        $this->codeClient = $codeClient;

        return $this;
    }

    public function getClient(): ?string
    {
        return $this->client;
    }

    public function setClient(?string $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getPoste(): ?int
    {
        return $this->poste;
    }

    public function setPoste(int $poste): static
    {
        $this->poste = $poste;

        return $this;
    }

    public function getOperation(): ?string
    {
        return $this->operation;
    }

    public function setOperation(string $operation): static
    {
        $this->operation = $operation;

        return $this;
    }

    public function getClotureCaissier(): ?string
    {
        return $this->clotureCaissier;
    }

    public function setClotureCaissier(?string $clotureCaissier): static
    {
        $this->clotureCaissier = $clotureCaissier;

        return $this;
    }

    public function getClotureGlobale(): ?string
    {
        return $this->clotureGlobale;
    }

    public function setClotureGlobale(?string $clotureGlobale): static
    {
        $this->clotureGlobale = $clotureGlobale;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

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

    public function setLogAction(?string $log_action): static
    {
        $this->log_action = $log_action;

        return $this;
    }

}
