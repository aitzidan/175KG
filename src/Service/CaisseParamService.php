<?php

namespace App\Service;

use App\Entity\Caisse;
use App\Repository\CaisseRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

class CaisseParamService
{
    private $em;
    private $caisseRepo;
    private $conn;

    public function __construct(EntityManagerInterface $em, Connection $conn, CaisseRepository $caisseRepo)
    {
        $this->em = $em;
        $this->caisseRepo = $caisseRepo;
        $this->conn = $conn;
    }

    public function checkData($data): bool
    {
        foreach (['caisse'] as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }

        return true;
    }

    public function addCaisse($data): Caisse
    {
        $caisse = new Caisse();
        $caisse->setCaisse($data['caisse']);
    
        $this->em->persist($caisse);
        $this->em->flush();
    
        return $caisse;
    }
    
    public function saveCaisse($data, ?Caisse $caisse = null): Caisse
    {
        if ($caisse === null) {
            $caisse = new Caisse();
        }
    
        $caisse->setCaisse($data['caisse']);
        $this->em->flush();
    
        return $caisse;
    }
    
    public function getCaisse($id): ?Caisse
    {
        return $this->caisseRepo->find($id);
    }

    public function deleteCaisse($caisse): void
    {
        $this->em->remove($caisse);
        $this->em->flush();
    }

    public function getListCaisse(): array
    {
        return $this->caisseRepo->findAll();
    }
}
