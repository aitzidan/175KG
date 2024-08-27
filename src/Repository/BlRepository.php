<?php

namespace App\Repository;

use App\Entity\Bl;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @extends ServiceEntityRepository<Bl>
 */
class BlRepository extends ServiceEntityRepository
{
    public $_em;
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $_em)
    {
        parent::__construct($registry, Bl::class);
        $this->_em = $_em;
    }

    //    /**
    //     * @return Bl[] Returns an array of Bl objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Bl
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findMaxBl() {
        $query = $this->_em->createQuery('SELECT MAX(d.id) AS max_id FROM App\Entity\Bl d');
        $result = $query->getSingleScalarResult(); // Use getSingleScalarResult() if you expect a single result.
        return $result;
    }

}
