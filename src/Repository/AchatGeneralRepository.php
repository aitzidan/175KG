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
        $query = $this->em->createQuery(
            'SELECT d 
             FROM App\Entity\AchatGeneral d
             WHERE d.id_fournisseur = :fournisseurId'
        )
        ->setParameter('fournisseurId', $fournisseurId);
    
        $results = $query->getResult();
    
        // Filter results by month and year
        $filteredResults = array_filter($results, function ($item) use ($month, $year) {
            $date = $item->getDate(); // Assuming you have a getDate() method
            return $date->format('m') == $month && $date->format('Y') == $year;
        });
    
        return $filteredResults;
    }
    
}
