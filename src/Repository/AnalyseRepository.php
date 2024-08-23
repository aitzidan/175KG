<?php

namespace App\Repository;

use App\Entity\Analyse;
use App\Entity\DetailBalance;
use App\Entity\DetailCaisse;
use App\Entity\Fichier;
use App\Entity\TypeFichier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Analyse>
 */
class AnalyseRepository extends ServiceEntityRepository
{
    public $em;
    public $conn;
    public function __construct(ManagerRegistry $registry , EntityManagerInterface $em , Connection $conn)
    {
        parent::__construct($registry, Analyse::class);
        $this->em = $em;
        $this->conn = $conn;
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
    public function getType($id){
        return $this->em->getRepository(TypeFichier::class)->find($id);
    }

    public function save(Analyse $analyse): void
    {
        $this->em->persist($analyse);
        $this->em->flush();
    }
    public function saveFile(Fichier $file): void
    {
        $this->em->persist($file);
        $this->em->flush();
    }
    public function getFichier($id)
    {
        return $this->em->getRepository(Fichier::class)->find($id);
    }
    
    public function getBalanceByAnalyse($id){
        return $this->em->getRepository(DetailBalance::class)->findBy(['id_analyse'=>$id]);
    }

    public function getPoidsCaisse($codeArticle , $id_analyse){
        $sql="SELECT SUM(qte) FROM `detail_caisse` where code = '".$codeArticle."' and id_analyse_id = ".$id_analyse." ";
        $stmt = $this->conn->prepare($sql);
        $stmt = $stmt->executeQuery();
        $resulat = $stmt->fetchOne();
        return $resulat;
    }

    public function saveBalance(DetailBalance $balance): void
    {
        $this->em->persist($balance);
        $this->em->flush();
    }
    public function getPrixCaisse($codeArticle , $id_analyse){
        $sql="SELECT SUM(prix_v) FROM `detail_caisse` where code = ".$codeArticle." and id_analyse_id = ".$id_analyse." ";
        $stmt = $this->conn->prepare($sql);
        $stmt = $stmt->executeQuery();
        $resulat = $stmt->fetchOne();
        return $resulat;
    }

    public function getCaisseByAnalyse($id){
        return $this->em->getRepository(DetailCaisse::class)->findBy(['id_analyse'=>$id]);
    }
    public function getBalanceByCode($code , $id_analyse){
        return $this->em->getRepository(DetailBalance::class)->findOneBy(['code'=>$code ,'id_analyse'=>$id_analyse]);
    }
    
    public function saveCaisse(DetailCaisse $caisse): void
    {
        $this->em->persist($caisse);
        $this->em->flush();
    }

    public function getDetailsIllicar(DetailCaisse $caisse): void
    {
        $array = array();
        
    }
    public function saveAnalyse(Analyse $analyse): void
    {
        $this->em->persist($analyse);
        $this->em->flush();
    }
    public function getPoidsCaisseTotal( $id_analyse){
        $sql="SELECT SUM(qte) FROM `detail_caisse` where id_analyse_id = ".$id_analyse." ";
        $stmt = $this->conn->prepare($sql);
        $stmt = $stmt->executeQuery();
        $resulat = $stmt->fetchOne();
        return $resulat;
    }

    public function getPrixCaisseTotal( $id_analyse){
        $sql="SELECT SUM(prix_v) FROM `detail_caisse` where  id_analyse_id = ".$id_analyse." ";
        $stmt = $this->conn->prepare($sql);
        $stmt = $stmt->executeQuery();
        $resulat = $stmt->fetchOne();
        return $resulat;
    }

    public function getPrixBalanceTotal( $id_analyse){
        $sql="SELECT SUM(montant) FROM `detail_balance` where  id_analyse_id = ".$id_analyse." ";
        $stmt = $this->conn->prepare($sql);
        $stmt = $stmt->executeQuery();
        $resulat = $stmt->fetchOne();
        return $resulat;
    }

    public function getPoidsBalanceTotal( $id_analyse){
        $sql="SELECT SUM(poids) FROM `detail_balance` where  id_analyse_id = ".$id_analyse." ";
        $stmt = $this->conn->prepare($sql);
        $stmt = $stmt->executeQuery();
        $resulat = $stmt->fetchOne();
        return $resulat;
    }
    public function getAllHisto(){
        $resultList = $this->em->getRepository(Analyse::class)->findBy([],["id"=>"DESC"]);
        return $resultList;
    }


}
