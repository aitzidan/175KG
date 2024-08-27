<?php

namespace App\Service;

use App\Entity\Entity;
use App\Repository\EntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

class EntityService
{
    private $em;
    private $entityRepo;
    private $conn;

    public function __construct(EntityManagerInterface $em, Connection $conn, EntityRepository $entityRepo)
    {
        $this->em = $em;
        $this->entityRepo = $entityRepo;
        $this->conn = $conn;
    }

    public function checkData($data): bool
    {
        foreach (['entity'] as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }
        return true;
    }

    public function addEntity($data): Entity
    {
        $entity = new Entity();
        $entity->setEntity($data['entity']);
        $entity->setDateCreation(new \DateTime('now'));
    
        $this->em->persist($entity);
        $this->em->flush();
    
        return $entity;
    }

    public function saveEntity($data, ?Entity $entity = null): Entity
    {
        if ($entity === null) {
            $entity = new Entity();
        }
    
        $entity->setEntity($data['entity']);
        $this->em->flush();
    
        return $entity;
    }

    public function getEntity($id)
    {
        return $this->entityRepo->find($id);
    }

    public function deleteEntity($entity)
    {
        $this->em->remove($entity);
        $this->em->flush();
    }

    public function getListEntity()
    {
        return $this->entityRepo->findAll();
    }
}
