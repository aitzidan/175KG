<?php

namespace App\Service;

use App\Entity\CategorieMagasin;
use App\Entity\Categorie;
use App\Repository\CategorieMagasinRepository;
use App\Repository\CategorieRepository;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class CategorieService
{
    private $em;
    private $categorieRepo;
    private $conn;

    public function __construct(EntityManagerInterface $em , Connection $conn ,  CategorieRepository $categorieRepo )
    {
        $this->em = $em;
        $this->categorieRepo = $categorieRepo;
        $this->conn = $conn;
    }

    public function checkData($data): bool
    {
        // Check if all fields are present and not empty
        foreach (['categorie'] as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }


        return true;
    }
    public function addCategorie($data): Categorie
    {
        $categorie = new Categorie();
        $categorie->setDateCreation(new \DateTime('now'));

        // Set data to the CategorieMagasin entity
        $categorie->setCategorie($data['categorie']);
    
        $this->em->persist($categorie);
    
        $this->em->flush();
    
        return $categorie;
    }
    


    public function saveCategorie($data, ?Categorie $categorie = null): Categorie
    {
        if ($categorie === null) {
            $categorie = new Categorie();
            $categorie->setDateCreation(new \DateTime('now'));
        }
    
        // Set data to the CategorieMagasin entity
        $categorie->setCategorie($data['categorie']);
        $this->em->flush();
    
        return $categorie;
    }
    


    public function getCategorie($id){
        return $this->categorieRepo->find($id);
    }

    public function deleteCategorie($categorie){
        $this->em->remove($categorie);
        $this->em->flush();
    }

    public function getListCategorie(){
        return $this->categorieRepo->findAll();
    }

    
    
    


}