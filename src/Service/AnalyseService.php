<?php

namespace App\Service;

use App\Entity\Analyse;
use App\Entity\Fichier;
use App\Repository\AnalyseRepository;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\File\File;

class AnalyseService
{
    private $session;
    private $defaultImagePath = '/assets/images/Users/user.png';
    private $uploadDir;
    private $entityManager;
    private $analyseRepo;
    private $conn;

    public function __construct(EntityManagerInterface $entityManager , Connection $conn ,  AnalyseRepository $analyseRepo)
    {
        $this->session = new Session();
        $this->entityManager = $entityManager;
        $this->analyseRepo = $analyseRepo;
        $this->conn = $conn;
    }

    public function isConnected(): bool
    {
        return $this->session->get('user_id') !== null;
    }

    public function getTypeFichier()
    {
        return $this->analyseRepo->getTypeFichier();
    }

    public function getType($id)
    {
        return $this->analyseRepo->getType($id);
    }

    public function addAnalyse($titre , $date_debut , $date_fin){
        $analyse = new Analyse();
        $analyse->setTitre($titre);
        $analyse->setDateDebut($date_debut);
        $analyse->setDateFin($date_fin);
        $this->analyseRepo->save($analyse);
        return $analyse;
    }
    public function addFichier($analyse , $type , $url){
        $fichier = new Fichier();
        $type = $this->getType($type);
        $fichier->setType($type);
        $fichier->setUrl($url);
        $fichier->setIdAnalyse($analyse);
        $this->analyseRepo->saveFile($fichier);
        return $fichier;
    }
    public function getFichier($id){
        $fichier = $this->analyseRepo->getFichier($id);
        return $fichier;
    }

    public function sauvguardeDataCSV($id, $type, $id_analyse)
    {
        $codeStatut = "ERROR";

        // Retrieve the file URL
        $import = $this->getFichier($id);
        $url = $import->getUrl();
        $file = new File($url);
        $realPath = str_replace("\\", "/", $file->getRealPath());

        // Open the CSV file for reading to get the header
        $fileHandle = fopen($realPath, 'r');
        $header = fgetcsv($fileHandle, 0, ';');
        fclose($fileHandle);

        $entiteCheck = '';
        $setForEntete = '';
        $liaison = ', ';

        for ($j = 0; $j < count($header); $j++) {
            $entiteCheck .= '@' . $header[$j] . $liaison;
            $setForEntete .= $header[$j] . ' = @' . $header[$j] . ' ' . $liaison;
        }

        $entiteCheck = rtrim($entiteCheck, $liaison);
        $setForEntete = rtrim($setForEntete, $liaison);

        $table = ( $type == 1 ) ? 'detail_balance' : 'detail_caisse';

        $sql = "LOAD DATA INFILE '$realPath' INTO TABLE ".$table." 
            FIELDS TERMINATED BY ';' 
            ENCLOSED BY '\"' 
            LINES TERMINATED BY '\n' 
            IGNORE 1 ROWS 
            ($entiteCheck)
            SET $setForEntete, id_analyse_id = '$id_analyse'";
        $this->executeSQL($sql);
        $codeStatut = "OK";
       
        return $codeStatut;
    }


    public function executeSQL($sql){
        $stmt = $this->conn->prepare($sql); 
        $stmt = $stmt->executeQuery();
    }

    public function getBalanceByAnalyse($id)
    {
        return $this->analyseRepo->getBalanceByAnalyse($id);
    }
    public function getPoidsCaisse($codeArticle , $id_analyse)
    {
        return $this->analyseRepo->getPoidsCaisse($codeArticle , $id_analyse);
    }
    public function majBalance($balance , $etatPoids , $etatPrix, $log_action ,$codeArticle):void
    {
        $balance->setLogAction($log_action);
        $balance->setEtatPoids($etatPoids);
        $balance->setEtatPrix($etatPrix);
        $balance->setCode($codeArticle);
        $this->analyseRepo->saveBalance($balance);
    }
    public function getPrixCaisse($codeArticle , $id_analyse)
    {
        return $this->analyseRepo->getPrixCaisse($codeArticle , $id_analyse);
    }
    public function getCaisseByAnalyse($id)
    {
        return $this->analyseRepo->getCaisseByAnalyse($id);
    }
    public function getBalanceByCode($code , $id_analyse)
    {
        return $this->analyseRepo->getBalanceByCode($code , $id_analyse);
    }
    
}