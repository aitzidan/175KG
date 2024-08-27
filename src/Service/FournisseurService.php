<?php

namespace App\Service;

use App\Entity\Fournisseur;
use App\Repository\FournisseurRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

class FournisseurService
{
    private $em;
    private $fournisseurRepo;
    private $conn;

    public function __construct(EntityManagerInterface $em, Connection $conn, FournisseurRepository $fournisseurRepo)
    {
        $this->em = $em;
        $this->fournisseurRepo = $fournisseurRepo;
        $this->conn = $conn;
    }

    public function checkData($data): bool
    {
        // Check if all required fields are present and not empty
        foreach (['rs', 'telephone', 'email'] as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }

        return true;
    }

    public function addFournisseur($data): Fournisseur
    {
        $fournisseur = new Fournisseur();
        $fournisseur->setRs($data['rs']);
        $fournisseur->setTelephone($data['telephone']);
        $fournisseur->setEmail($data['email']);
        $fournisseur->setAdresse($data['adresse'] ?? null);
        $fournisseur->setNom($data['nom'] ?? null);
        $fournisseur->setPrenom($data['prenom'] ?? null);
        $fournisseur->setIce($data['ice'] ?? null);
        $fournisseur->setRc($data['rc'] ?? null);

        $this->em->persist($fournisseur);
        $this->em->flush();

        return $fournisseur;
    }

    public function saveFournisseur($data, ?Fournisseur $fournisseur = null): Fournisseur
    {
        if ($fournisseur === null) {
            $fournisseur = new Fournisseur();
        }

        $fournisseur->setRs($data['rs']);
        $fournisseur->setTelephone($data['telephone']);
        $fournisseur->setEmail($data['email']);
        $fournisseur->setAdresse($data['adresse'] ?? null);
        $fournisseur->setNom($data['nom'] ?? null);
        $fournisseur->setPrenom($data['prenom'] ?? null);
        $fournisseur->setIce($data['ice'] ?? null);
        $fournisseur->setRc($data['rc'] ?? null);

        $this->em->flush();

        return $fournisseur;
    }

    public function getFournisseur($id)
    {
        return $this->fournisseurRepo->find($id);
    }

    public function deleteFournisseur($fournisseur)
    {
        $this->em->remove($fournisseur);
        $this->em->flush();
    }

    public function getListFournisseur()
    {
        return $this->fournisseurRepo->findAll();
    }
}
