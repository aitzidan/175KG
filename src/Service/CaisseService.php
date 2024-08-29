<?php

namespace App\Service;

use App\Entity\CaisseMagasin;
use App\Repository\CaisseMagasinRepository;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class CaisseService
{
    private $em;
    private $caisseRepo;
    private $conn;
    private $CaisseParamService;
    public function __construct(EntityManagerInterface $em , Connection $conn ,  CaisseMagasinRepository $caisseRepo , CaisseParamService $CaisseParamService)
    {
        $this->em = $em;
        $this->caisseRepo = $caisseRepo;
        $this->conn = $conn;
        $this->CaisseParamService = $CaisseParamService;
    }

    public function checkData($data): bool
    {
        // Check if all required fields are set
        foreach (['date', 'tpe', 'espece', 'amount', 'espece_final',  'caisse','tpe_naps','ecart'] as $field) {
            if (!isset($data[$field])) {
                return false;
            }
            
        }
        foreach (['caisse'] as $field) {
            if (empty($data[$field])) {
                return false;
            }
            
        }

        // Check if tpe, espece, amount, and espece_final are numeric
        if (!is_numeric($data['tpe']) || !is_numeric($data['espece']) || 
            !is_numeric($data['amount']) || !is_numeric($data['espece_final'])) {
            return false;
        }

        return true;
    }

    public function addCaisse($data): CaisseMagasin
    {
        $caisse = new CaisseMagasin();
        $caisse->setDateCreation(new \DateTime('now'));

        $caisseParam = $this->CaisseParamService->getCaisse($data['caisse']);

        // Set data to the CaisseMagasin entity
        $caisse->setTpe($data['tpe']);
        $caisse->setEspece($data['espece']);
        $caisse->setAmount($data['amount']);
        $caisse->setEspeceFinal($data['espece_final']);
        $caisse->setDate(new \DateTime($data['date']));
        $caisse->setTpeNaps($data['tpe_naps']);
        $caisse->setEcart($data['ecart']);
        $caisse->setIdCaisse($caisseParam);
        $caisse->setEtat(0);
    
        $this->em->persist($caisse);
    
        $this->em->flush();
    
        return $caisse;
    }
    


    public function saveCaisse($data, ?CaisseMagasin $caisse = null): CaisseMagasin
    {
        if ($caisse === null) {
            $caisse = new CaisseMagasin();
            $caisse->setDateCreation(new \DateTime('now'));
        }

        $caisseParam = $this->CaisseParamService->getCaisse($data['caisse']);
    
        // Set data to the CaisseMagasin entity
        $caisse->setTpe($data['tpe']);
        $caisse->setEspece($data['espece']);
        $caisse->setAmount($data['amount']);
        $caisse->setEspeceFinal($data['espece_final']);
        $caisse->setDate(new \DateTime($data['date']));
        $caisse->setTpeNaps($data['tpe_naps']);
        $caisse->setEcart($data['ecart']);
        $caisse->setIdCaisse($caisseParam);
    
        // Persist and flush only if it's a new entity
        if ($this->em->getRepository(CaisseMagasin::class)->find($caisse->getId()) === null) {
            $this->em->persist($caisse);
        }
    
        $this->em->flush();
    
        return $caisse;
    }
    
    public function getDataByFilter($filterType, $dateDebut, $dateFin, $annee, $mois)
    {
        $sql = 'SELECT c.* FROM caisse_magasin c';
    
        // Filter by date range
        if ($filterType === 'date') {
            $dateDebut = $dateDebut . " 00:00:00";
            $dateFin = $dateFin . " 23:59:59";
            $sql .= ' WHERE c.date BETWEEN :dateDebut AND :dateFin';
        }
    
        // Filter by year and optionally month
        if ($filterType === 'year') {
            $sql .= ' WHERE YEAR(c.date) = :annee';
            if ($mois) {
                $sql .= ' AND MONTH(c.date) = :mois';
            }
        }
    
        $stmt = $this->conn->prepare($sql);
    
        // Bind parameters
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
        $resulat = $stmt->fetchAll();
        return $resulat;
    }

    public function getTheEspece($filterType, $dateDebut, $dateFin, $annee, $mois)
    {
        $sql = 'SELECT SUM(c.espece_final) FROM caisse_magasin c';
    
        // Filter by date range
        if ($filterType === 'date') {
            $dateDebut = $dateDebut . " 00:00:00";
            $dateFin = $dateFin . " 23:59:59";
            $sql .= ' WHERE c.date BETWEEN :dateDebut AND :dateFin';
        }
    
        // Filter by year and optionally month
        if ($filterType === 'year') {
            $sql .= ' WHERE YEAR(c.date) = :annee';
            if ($mois) {
                $sql .= ' AND MONTH(c.date) = :mois';
            }
        }
    
        $stmt = $this->conn->prepare($sql);
    
        // Bind parameters
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
        $resulat = $stmt->fetchOne();
        return $resulat;
    }
    
    public function getTpe($filterType, $dateDebut, $dateFin, $annee, $mois)
    {
        $sql = 'SELECT SUM(c.tpe) FROM caisse_magasin c';
    
        // Filter by date range
        if ($filterType === 'date') {
            $dateDebut = $dateDebut . " 00:00:00";
            $dateFin = $dateFin . " 23:59:59";
            $sql .= ' WHERE c.date BETWEEN :dateDebut AND :dateFin';
        }
    
        // Filter by year and optionally month
        if ($filterType === 'year') {
            $sql .= ' WHERE YEAR(c.date) = :annee';
            if ($mois) {
                $sql .= ' AND MONTH(c.date) = :mois';
            }
        }
    
        $stmt = $this->conn->prepare($sql);
    
        // Bind parameters
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
        $resulat = $stmt->fetchOne();
        return $resulat;
    }
    
    public function getEcart($filterType, $dateDebut, $dateFin, $annee, $mois)
    {
        $sql = 'SELECT SUM(c.ecart) FROM caisse_magasin c';
    
        // Filter by date range
        if ($filterType === 'date') {
            $dateDebut = $dateDebut . " 00:00:00";
            $dateFin = $dateFin . " 23:59:59";
            $sql .= ' WHERE c.date BETWEEN :dateDebut AND :dateFin';
        }
    
        // Filter by year and optionally month
        if ($filterType === 'year') {
            $sql .= ' WHERE YEAR(c.date) = :annee';
            if ($mois) {
                $sql .= ' AND MONTH(c.date) = :mois';
            }
        }
    
        $stmt = $this->conn->prepare($sql);
    
        // Bind parameters
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
        $resulat = $stmt->fetchOne();
        return $resulat;
    }
    

    public function getDataByFilter2( $dateDebut, $dateFin)
    {
        $sql = 'SELECT c.* FROM caisse_magasin c';
    
        // Filter by date range
        $dateDebut = $dateDebut . " 00:00:00";
        $dateFin = $dateFin . " 23:59:59";
        $sql .= ' WHERE c.date BETWEEN :dateDebut AND :dateFin';

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue('dateDebut', $dateDebut);
        $stmt->bindValue('dateFin', $dateFin);

      
        $stmt = $stmt->executeQuery();
        $resulat = $stmt->fetchAll();
        return $resulat;
    }

    public function getTheEspece2( $dateDebut, $dateFin)
    {
        $sql = 'SELECT SUM(c.espece_final) FROM caisse_magasin c';
    
        // Filter by date range
        $dateDebut = $dateDebut . " 00:00:00";
        $dateFin = $dateFin . " 23:59:59";
        $sql .= ' WHERE c.date BETWEEN :dateDebut AND :dateFin';

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue('dateDebut', $dateDebut);
        $stmt->bindValue('dateFin', $dateFin);
    
        $stmt = $stmt->executeQuery();
        $resulat = $stmt->fetchOne();
        return $resulat;
    }
    
    public function getTpe2($dateDebut, $dateFin)
    {
        $sql = 'SELECT SUM(c.tpe) FROM caisse_magasin c';
        // Filter by date range
        $dateDebut = $dateDebut . " 00:00:00";
        $dateFin = $dateFin . " 23:59:59";
        $sql .= ' WHERE c.date BETWEEN :dateDebut AND :dateFin';

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue('dateDebut', $dateDebut);
        $stmt->bindValue('dateFin', $dateFin);
    
        $stmt = $stmt->executeQuery();
        $resulat = $stmt->fetchOne();
        return $resulat;
    }

    public function getEcart2($dateDebut, $dateFin)
    {
        $sql = 'SELECT SUM(c.ecart) FROM caisse_magasin c';
        // Filter by date range
        $dateDebut = $dateDebut . " 00:00:00";
        $dateFin = $dateFin . " 23:59:59";
        $sql .= ' WHERE c.date BETWEEN :dateDebut AND :dateFin';

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue('dateDebut', $dateDebut);
        $stmt->bindValue('dateFin', $dateFin);
    
        $stmt = $stmt->executeQuery();
        $resulat = $stmt->fetchOne();
        return $resulat;
    }
    public function getCaisse($id){
        return $this->caisseRepo->find($id);
    }

    public function deleteCaisse($caisse){
        $this->em->remove($caisse);
        $this->em->flush();
    }

    public function validateCaisse($caisse){
        $caisse->setEtat(1);
        $this->em->flush();
    }


}