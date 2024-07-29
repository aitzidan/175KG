<?php

namespace App\Repository;

use App\Entity\Analyse;
use App\Entity\TypeFichier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Analyse>
 */
class AnalyseRepository extends ServiceEntityRepository
{
    public $em;
    public function __construct(ManagerRegistry $registry , EntityManagerInterface $em)
    {
        parent::__construct($registry, Analyse::class);
        $this->em = $em;
    }

    //    /**
    //     * @return Analyse[] Returns an array of Analyse objects
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

    //    public function findOneBySomeField($value): ?Analyse
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function getTypeFichier(){
        return $this->em->getRepository(TypeFichier::class)->findAll();
    }
}
