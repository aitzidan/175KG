<?php

namespace App\Service;

use App\Entity\DesignationMagasin;
use App\Entity\Designation;
use App\Repository\CategorieRepository;
use App\Repository\DesignationMagasinRepository;
use App\Repository\DesignationRepository;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class DesignationService
{
    private $em;
    private $designationRepo;
    private $categorieRepo;
    private $conn;

    public function __construct(EntityManagerInterface $em , Connection $conn ,  DesignationRepository $designationRepo, CategorieRepository $categorieRepo )
    {
        $this->em = $em;
        $this->designationRepo = $designationRepo;
        $this->categorieRepo = $categorieRepo;
        $this->conn = $conn;
    }

    public function checkData($data): bool
    {
        // Check if all fields are present and not empty
        foreach (['designation'] as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }
        return true;
    }
    public function addDesignation($data): Designation
    {
        $categorie = $this->categorieRepo->find($data['categorie']);
        $designation = new Designation();
        $designation->setDateCreation(new \DateTime('now'));
        $designation->setIdCategorie($categorie);

        // Set data to the DesignationMagasin entity
        $designation->setDesignation($data['designation']);
    
        $this->em->persist($designation);
    
        $this->em->flush();
    
        return $designation;
    }
    


    public function saveDesignation($data, ?Designation $designation = null): Designation
    {
        if ($designation === null) {
            $designation = new Designation();
            $designation->setDateCreation(new \DateTime('now'));
        }
        $categorie = $this->categorieRepo->find($data['categorie']);
        $designation->setIdCategorie($categorie);
        // Set data to the DesignationMagasin entity
        $designation->setDesignation($data['designation']);
        $this->em->flush();
    
        return $designation;
    }
    


    public function getDesignation($id){
        return $this->designationRepo->find($id);
    }

    public function deleteDesignation($designation){
        $this->em->remove($designation);
        $this->em->flush();
    }

    public function getListDesignation(){
        return $this->designationRepo->findAll();
    }

    public function getDesignationByCat($id){
        return $this->designationRepo->getDesignationByCat($id);
    }


}