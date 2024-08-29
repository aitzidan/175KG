<?php

namespace App\Service;

use App\Entity\DetailRevient;
use App\Entity\Revient;
use App\Repository\DetailRevientRepository;
use App\Repository\RevientRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

class RevientService
{
    private $em;
    private $revientRepo;
    private $conn;


    public function __construct(
        private FournisseurService $FournisseurService,
        private DetailRevientRepository $detailRevientRepo,
        EntityManagerInterface $em, Connection $conn, RevientRepository $revientRepo)
    {
        $this->em = $em;
        $this->revientRepo = $revientRepo;
        $this->conn = $conn;
    }

    


    public function checkData($data): bool
    {
        // Check if all required fields are present and not empty
        foreach (['nom_produit', 'nombre_unite', 'total_ht', 'prix_revient_ht', 'prix_vente_ht', 'marge_brute', 'taux_marge', 'coefficient_marge'] as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }
        return true;
    }

    public function checkDetails($data): bool
    {
        // Check if all required fields are present and not empty
        foreach (['designation', 'fournisseur', 'unite', 'cout_achat', 'prix_vente_ht', 'unite_necessaire', 'prix_ht'] as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }
        return true;
    }


    public function addRevient($data, $details): Revient
    {
        $revient = new Revient();

        // Set data to the Revient entity
        $revient->setNomProduit($data['nom_produit']);
        $revient->setNombreUnite($data['nombre_unite']);
        $revient->setTotalHt($data['total_ht']);
        $revient->setPrixHt($data['prix_revient_ht']);
        $revient->setPrixVenteHt($data['prix_vente_ht']);
        $revient->setMargeBrute($data['marge_brute']);
        $revient->setTauxMarge($data['taux_marge']);
        $revient->setCoefficientMarge($data['coefficient_marge']);

        for ($i=0; $i <count($details) ; $i++) { 
            
            $detail = new DetailRevient();
            $fournisseur = $this->FournisseurService->getFournisseur($details[$i]['fournisseur']);
            
            $detail->setDesignation($details[$i]['designation']);
            $detail->setFournisseur($fournisseur);
            $detail->setCoutAchat($details[$i]['cout_achat']);
            $detail->setPrixHt($details[$i]['prix_ht']);
            $detail->setUnite($details[$i]['unite']);
            $detail->setUniteNecessaire($details[$i]['unite_necessaire']);

            $detail->setIdRevient($revient);
            $this->em->persist($detail);

        }

        $this->em->persist($revient);
        $this->em->flush();

        return $revient;
    }

    public function saveRevient($data, $details,?Revient $revient = null): Revient
    {
        if ($revient === null) {
            $revient = new Revient();
        }

        // Set data to the Revient entity
        $revient->setNomProduit($data['nom_produit']);
        $revient->setNombreUnite($data['nombre_unite']);
        $revient->setTotalHt($data['total_ht']);
        $revient->setPrixHt($data['prix_ht']);
        $revient->setPrixVenteHt($data['prix_vente_ht']);
        $revient->setMargeBrute($data['marge_brute']);
        $revient->setTauxMarge($data['taux_marge']);
        $revient->setCoefficientMarge($data['coefficient_marge']);

        $this->em->flush();

        return $revient;
    }

    public function getRevient($id)
    {
        return $this->revientRepo->find($id);
    }

    public function deleteRevient($revient)
    {
        $this->em->remove($revient);
        $this->em->flush();
    }

    public function getListRevient()
    {
        return $this->revientRepo->findAll();
    }

    public function getDetailsRevient($id)
    {
        return $this->detailRevientRepo->findByIdRevient($id);
    }
}
