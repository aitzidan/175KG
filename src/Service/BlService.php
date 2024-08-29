<?php

namespace App\Service;

use App\Entity\Bl;
use App\Entity\DetailBl;
use App\Repository\BlRepository;
use App\Repository\DetailBlRepository;
use App\Repository\EntityRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;

class BlService
{
    private $em;
    private $blRepo;
    private $EntityRepo;
    private $ProduitRepo;

    private $DetailRepo;

    public function __construct(EntityManagerInterface $em, BlRepository $blRepo , EntityRepository $EntityRepo, ProduitRepository $ProduitRepo, DetailBlRepository $DetailRepo)
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
}
