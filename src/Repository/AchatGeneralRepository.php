<?php

namespace App\Repository;

use App\Entity\AchatGeneral;
use App\Entity\Categorie;
use App\Entity\Fournisseur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AchatGeneral>
 */
class AchatGeneralRepository extends ServiceEntityRepository
{
    

    public $em;
    public function __construct(ManagerRegistry $registry , EntityManagerInterface $em )
    {
        parent::__construct($registry, AchatGeneral::class);
        $this->em = $em;
    }


//    /**
//     * @return AchatGeneral[] Returns an array of AchatGeneral objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?AchatGeneral
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
  

}
