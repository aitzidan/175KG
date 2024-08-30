<?php

namespace App\Service;

use App\Entity\Bl;
use App\Entity\DetailBl;
use App\Repository\BlRepository;
use App\Repository\DetailBlRepository;
use App\Repository\EntityRepository;
use App\Repository\ProduitRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

class BlService
{
    private $em;
    private $blRepo;
    private $EntityRepo;
    private $ProduitRepo;

    private $DetailRepo;

    public function __construct(
        private Connection $conn,
        EntityManagerInterface $em, BlRepository $blRepo , EntityRepository $EntityRepo, ProduitRepository $ProduitRepo, DetailBlRepository $DetailRepo)
    {
        $this->em = $em;
        $this->blRepo = $blRepo;
        $this->EntityRepo = $EntityRepo;
        $this->ProduitRepo = $ProduitRepo;
        $this->DetailRepo = $DetailRepo;
    }

    public function checkData($data): bool
    {
        // Check if all fields are present and not empty
        foreach (['date', 'code', 'entity'] as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }
        return true;
    }

    public function checkDataDetails($data): bool
    {
        // Check if all fields are present and not empty
        foreach (['idProduit', 'designation', 'qte', 'unite'] as $field) {
            if (!isset($data[$field])) {
                return false;
            }
        }
        return true;
    }

    

    public function addBl($data , $details): Bl
    {
        $bl = new Bl();
        $bl->setDate(new \DateTime($data['date']));
        $bl->setCode($data['code']);

        $entity = $this->EntityRepo->find($data['entity']);
        $bl->setEntity($entity);

        for ($i=0; $i <count($details) ; $i++) { 
            $idProduit = $details[$i]['idProduit'];
            $produit = $this->ProduitRepo->find($idProduit);

            $detail = new DetailBl();
            $detail->setIdProduit($produit);
            $detail->setQte($details[$i]['qte']);

            $detail->setIdBl($bl);
            $this->em->persist($detail);

        }

        $this->em->persist($bl);
        $this->em->flush();

        return $bl;
    }

    public function saveBl($data, $details, $bl ): Bl
    {

        $detailsBl = $this->getDetailsBl($bl->getId());

        foreach ($detailsBl as $d) {
            $this->deleteDetail($d);
        }

        $bl->setDate(new \DateTime($data['date']));
        $bl->setCode($data['code']);
        
        $entity = $this->EntityRepo->find($data['entity']);
        $bl->setEntity($entity);

        for ($i=0; $i <count($details) ; $i++) { 
            $idProduit = $details[$i]['idProduit'];
            $produit = $this->ProduitRepo->find($idProduit);

            $detail = new DetailBl();
            $detail->setIdProduit($produit);
            $detail->setQte($details[$i]['qte']);

            $detail->setIdBl($bl);
            $this->em->persist($detail);
        }

        $this->em->flush();
        return $bl;
    }

    public function getBl($id): ?Bl
    {
        return $this->blRepo->find($id);
    }

    public function deleteBl(Bl $bl): void
    {
        $detailsBl = $this->getDetailsBl($bl->getId());
        foreach ($detailsBl as $d) {
            $this->deleteDetail($d);
        }
        $this->em->remove($bl);
        $this->em->flush();
    }

    public function validateBl(Bl $bl): void
    {
        $bl->setEtat(1);
        $this->em->flush();
    }

    public function deleteDetail(DetailBl $bl): void
    {
        $this->em->remove($bl);
        $this->em->flush();
    }

    public function getListBl(): array
    {
        return $this->blRepo->findAll();
    }

    public function getDetailsBl($id){
        
        return $this->DetailRepo->findByIdBl($id);
    }
    public function findMaxBl(){
        
        return $this->blRepo->findMaxBl();
    }

    public function getDataBl($filterType, $dateDebut, $dateFin, $annee, $mois, $entitySelect)
    {
        $montant = 0;
        $sql = 'SELECT c.* FROM bl c where 1=1   ';
        
        
        // Filter by date range
        if ($filterType === 'date') {
            $dateDebut = $dateDebut . " 00:00:00";
            $dateFin = $dateFin . " 23:59:59";
            $sql .= ' AND c.date BETWEEN :dateDebut AND :dateFin';
        }
    
        // Filter by year and optionally month
        if ($filterType === 'year') {
            $sql .= ' AND YEAR(c.date) = :annee';
            if ($mois) {
                $sql .= ' AND MONTH(c.date) = :mois';
            }
        }

        // Filter by date range
        if ($entitySelect != '') {
            $sql .= ' AND c.entity_id = '.$entitySelect.'';
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

        $list = [];
        for ($i=0; $i < count($resulat) ; $i++) { 
            $list[$i] =  $resulat[$i];

            $qte = 0;
            $details = $this->getDetailsBl($resulat[$i]['id']);
            foreach ($details as $d) {
                $qte += $d->getQte();
            }
            $list[$i]['qte'] =  $qte;
            $montant += $qte;
        }

        $data['list'] = $list;
        $data['montant'] = $montant;

        return $data;
    }

}
