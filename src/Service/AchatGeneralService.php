<?php

namespace App\Service;

use App\Entity\AchatGeneral;
use App\Entity\Categorie;
use App\Entity\Fournisseur;
use App\Repository\AchatGeneralRepository;
use App\Repository\CategorieRepository;
use App\Repository\FournisseurRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

class AchatGeneralService
{
    private $em;
    private $achatGeneralRepo;
    private $fournisseurRepo;
    private $categorieRepo;
    private $conn;

    public function __construct(EntityManagerInterface $em , Connection $conn ,  AchatGeneralRepository $achatGeneralRepo, FournisseurRepository  $fournisseurRepo, CategorieRepository  $categorieRepo)
    {
        $this->em = $em;
        $this->achatGeneralRepo = $achatGeneralRepo;
        $this->conn = $conn;
        $this->fournisseurRepo = $fournisseurRepo;
        $this->categorieRepo = $categorieRepo;
        
    }
    public function checkData($data): bool
    {
        foreach (['date', 'categorie', 'designation', 'fournisseur', 'unite', 'prix'] as $field) {
            if (empty($data[$field])) {
                if ($data[$field] != 0) {
                    return false;
                }
            }
        }

        if (!is_numeric($data['qte']) || !is_numeric($data['prix']) || !is_numeric($data['montant'])) {
            return false;
        }

        return true;
    }

    public function addAchatGeneral($data): AchatGeneral
    {
        $achatGeneral = new AchatGeneral();
        $achatGeneral->setDate(new \DateTime($data['date']));

        $categorie = $this->categorieRepo->find($data['categorie']);
        $fournisseurs = $this->fournisseurRepo->find($data['fournisseur']);
        
        $achatGeneral->setCategorie($categorie);
        $achatGeneral->setIdFournisseur($fournisseurs);
        $achatGeneral->setDesignation($data['designation']);
        $achatGeneral->setUnite($data['unite']);
        $achatGeneral->setQte($data['qte']);
        $achatGeneral->setPrix($data['prix']);
        $achatGeneral->setMontant($data['montant']);

        $this->em->persist($achatGeneral);
        $this->em->flush();

        return $achatGeneral;
    }

    public function saveAchatGeneral($data, ?AchatGeneral $achatGeneral = null): AchatGeneral
    {
        if ($achatGeneral === null) {
            $achatGeneral = new AchatGeneral();
        }

        $achatGeneral->setDate(new \DateTime($data['date']));

        $categorie = $this->categorieRepo->find($data['categorie']);
        $fournisseurs = $this->fournisseurRepo->find($data['fournisseur']);
        $achatGeneral->setCategorie($categorie);
        $achatGeneral->setIdFournisseur($fournisseurs);
        $achatGeneral->setDesignation($data['designation']);
        $achatGeneral->setUnite($data['unite']);
        $achatGeneral->setQte($data['qte']);
        $achatGeneral->setPrix($data['prix']);
        $achatGeneral->setMontant($data['montant']);

        if ($this->em->getRepository(AchatGeneral::class)->find($achatGeneral->getId()) === null) {
            $this->em->persist($achatGeneral);
        }

        $this->em->flush();

        return $achatGeneral;
    }

    public function getAchatGeneralById($id)
    {
        return $this->achatGeneralRepo->find($id);
    }

    public function deleteAchatGeneral($achatGeneral)
    {
        $this->em->remove($achatGeneral);
        $this->em->flush();
    }



    public function getCategorie(){
        return $this->categorieRepo->findAll();
    }
    public function getOneCategorie($id):Categorie{
        return $this->categorieRepo->find($id);
    }

    public function getOneFournisseurs($id):Fournisseur{
        return $this->fournisseurRepo->find($id);
    }

    
    public function getFournisseurs(){
        return $this->fournisseurRepo->findAll();
    }

    public function getDateByFiltrage($filterType, $dateDebut, $dateFin, $annee, $mois , $fournisseur)
    {
        $sql = 'SELECT t.* FROM achat_general t where 1 = 1';

        if ($filterType === 'date') {
            $dateDebut = $dateDebut . " 00:00:00";
            $dateFin = $dateFin . " 23:59:59";
            $sql .= ' and t.date BETWEEN :dateDebut AND :dateFin';
        }

        if ($filterType === 'year') {
            $sql .= ' and YEAR(t.date) = :annee';
            if ($mois) {
                $sql .= ' AND MONTH(t.date) = :mois';
            }
        }
        if ($fournisseur) {
            $sql .= ' AND t.id_fournisseur_id = '.$fournisseur.'';
        }


        
        

        $stmt = $this->conn->prepare($sql);

        if ($filterType === 'date') {
            $stmt->bindValue('dateDebut', $dateDebut);
            $stmt->bindValue('dateFin', $dateFin);
        }

        if ($filterType === 'year') {
            $stmt->bindValue('annee', $annee);
            if ($mois) {
                $stmt->bindValue('mois', $mois);
            }
        }

        $stmt = $stmt->executeQuery();
        $result = $stmt->fetchAll();
        return $result;
    }

    public function getMontant($filterType, $dateDebut, $dateFin, $annee, $mois , $fournisseur)
    {
        $sql = 'SELECT SUM(t.montant) FROM achat_general t where 1 = 1';

        if ($filterType === 'date') {
            $dateDebut = $dateDebut . " 00:00:00";
            $dateFin = $dateFin . " 23:59:59";
            $sql .= ' and t.date BETWEEN :dateDebut AND :dateFin';
        }

        if ($filterType === 'year') {
            $sql .= ' and YEAR(t.date) = :annee';
            if ($mois) {
                $sql .= ' AND MONTH(t.date) = :mois';
            }
        }
        if ($fournisseur) {
            $sql .= ' AND t.id_fournisseur_id = '.$fournisseur.'';
        }

        $stmt = $this->conn->prepare($sql);

        if ($filterType === 'date') {
            $stmt->bindValue('dateDebut', $dateDebut);
            $stmt->bindValue('dateFin', $dateFin);
        }

        if ($filterType === 'year') {
            $stmt->bindValue('annee', $annee);
            if ($mois) {
                $stmt->bindValue('mois', $mois);
            }
        }

        $stmt = $stmt->executeQuery();
        $result = $stmt->fetchOne();
        return $result;
    }

    public function getAchatGeneral( $dateDebut, $dateFin)
    {
        $sql = 'SELECT t.* FROM achat_general t where 1 = 1';

        $dateDebut = $dateDebut . " 00:00:00";
        $dateFin = $dateFin . " 23:59:59";
        $sql .= ' and t.date BETWEEN :dateDebut AND :dateFin';

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue('dateDebut', $dateDebut);
        $stmt->bindValue('dateFin', $dateFin);

        $stmt = $stmt->executeQuery();
        $result = $stmt->fetchAll();
        return $result;
    }

    
    public function getMontantAchatGeneral( $dateDebut, $dateFin)
    {
        $sql = 'SELECT SUM(t.montant) FROM achat_general t where 1 = 1';

        $dateDebut = $dateDebut . " 00:00:00";
        $dateFin = $dateFin . " 23:59:59";
        $sql .= ' and t.date BETWEEN :dateDebut AND :dateFin';

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue('dateDebut', $dateDebut);
        $stmt->bindValue('dateFin', $dateFin);

        $stmt = $stmt->executeQuery();
        $result = $stmt->fetchOne();
        return $result;
    }


}