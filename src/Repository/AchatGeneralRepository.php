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
    
    private $connection;
    public $em;
    public function __construct(ManagerRegistry $registry , EntityManagerInterface $em )
    {
        parent::__construct($registry, AchatGeneral::class);
        $this->em = $em;
        $this->connection = $this->getEntityManager()->getConnection();
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
    
    // public function findByMonthYearAndFournisseur($month, $year, $fournisseurId)
    // {
    //     $sql = '
    //         SELECT id, categorie_id, id_fournisseur_id, date, unite, qte, prix, montant, id_designation_id, etat
    //         FROM achat_general
    //         WHERE MONTH(date) = :month
    //         AND YEAR(date) = :year
    //         AND id_fournisseur_id = :fournisseurId
    //     ';

    //     $stmt = $this->connection->prepare($sql);
    //     $res = $stmt->executeQuery([
    //         'month' => $month,
    //         'year' => $year,
    //         'fournisseurId' => $fournisseurId
    //     ]);

    //     return $res->fetchAllAssociative();
    // }
   

    public function findByMonthYearAndFournisseur($month, $year, $fournisseurId)
    {
        return $this->createQueryBuilder('a')
            ->where('MONTH(a.date) = :month')
            ->andWhere('YEAR(a.date) = :year')
            ->andWhere('a.id_fournisseur_id = :fournisseurId')
            ->setParameter('month', $month)
            ->setParameter('year', $year)
            ->setParameter('fournisseurId', $fournisseurId)
            ->getQuery()
            ->getResult();
    }



}
