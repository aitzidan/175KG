<?php

namespace App\Service;

use App\Entity\Analyse;
use App\Entity\DetailBalance;
use App\Entity\Fichier;
use App\Repository\AnalyseRepository;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\KernelInterface;

class AnalyseService
{
    private $session;
    private $defaultImagePath = '/assets/images/Users/user.png';
    private $uploadDir;
    private $entityManager;
    private $analyseRepo;
    private $conn;
    private $kernel;

    public function __construct(EntityManagerInterface $entityManager , Connection $conn ,  AnalyseRepository $analyseRepo , KernelInterface $kernel)
    {
        $this->session = new Session();
        $this->entityManager = $entityManager;
        $this->analyseRepo = $analyseRepo;
        $this->conn = $conn;
        $this->kernel = $kernel;
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
        $analyse->setDateCreation(new \DateTime("now"));
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
        stream_filter_append($fileHandle, 'convert.iconv.ISO-8859-1/UTF-8');
        $header = fgetcsv($fileHandle, 0, ';');
        fclose($fileHandle);

        $entiteCheck = '';
        $setForEntete = '';
        $liaison = ', ';

        for ($j = 0; $j < count($header); $j++) {
            if($header[$j] != ''){
                $entiteCheck .= '@' . $header[$j] . $liaison;
                if($header[$j] == 'Qté'){
                    $setForEntete .= 'qte' . ' = @' . $header[$j] . ' ' . $liaison;
                } else{
                    $setForEntete .= $header[$j] . ' = @' . $header[$j] . ' ' . $liaison;
                }
            }
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


    

    public function sauvguardeDataExcel($id, $type, $id_analyse)
    {
        $codeStatut = "ERROR";

        // Get file details and path
        $import = $this->getFichier($id);
        $url = $import->getUrl();
        $file = new File($url);
        $realPath = str_replace("\\", "/", $file->getRealPath());

        // Load the spreadsheet
        $spreadsheet = IOFactory::load($realPath);
        $sheet = $spreadsheet->getActiveSheet();

        // Generate a unique CSV file path
        $urlFile = "fichier/" . uniqid() . '.csv';
        $csvFilePath = $this->kernel->getProjectDir() . "/public/" . $urlFile;

        // Save the spreadsheet as CSV
        $csvWriter = new Csv($spreadsheet);
        $csvWriter->setSheetIndex($sheet->getParent()->getIndex($sheet));
        $csvWriter->save($csvFilePath);

        // Open the CSV file and skip to line 13 to read the data
        $fileHandle = fopen($csvFilePath, 'r');
        for ($i = 0; $i < 12; $i++) {
            fgets($fileHandle); // Skip lines
        }

        // Read the headers (first and second rows for merged headers)
        $header1 = fgetcsv($fileHandle, 0, ',');
        $header2 = fgetcsv($fileHandle, 0, ',');

        // Determine the table name based on the type
        $table = ($type == 1) ? 'detail_balance' : 'detail_caisse';

        // Read the data rows and insert them into the database
        while (($row = fgetcsv($fileHandle, 0, ',')) !== false) {
            
            if (preg_match('/^\d+-\w+/', $row[0])) {
                // Extract the necessary columns based on your headers
                $columns = [
                    $header1[0] => $row[0],
                    $header1[6] => $row[6],
                    $header1[9] => $row[9],
                    $header1[11] => $row[11],
                    $header1[13] => $row[13]
                ];
    
                // Construct the SQL insert statement
                $columnsSql = implode(', ', array_keys($columns));
                $valuesSql = implode(', ', array_map(function ($value) {
                    return "'" . addslashes($value) . "'";
                }, array_values($columns)));
    
                $sql = "INSERT INTO $table ($columnsSql, id_analyse_id) VALUES ($valuesSql, '$id_analyse');";
                
                // Execute the SQL insert statement
                $this->executeSQL($sql);
            }
    
        }

        fclose($fileHandle);

        // Remove the temporary CSV file
        unlink($csvFilePath);

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
    public function majBalance(DetailBalance $balance , $etatPoids , $etatPrix, $log_action ,$codeArticle, $ecart_poids, $ecart_prix):void
    {
        $balance->setLogAction($log_action);
        $balance->setEtatPoids($etatPoids);
        $balance->setEtatPrix($etatPrix);
        $balance->setCode($codeArticle);
        $balance->setEcartPoids($ecart_poids);
        $balance->setEcartPrix($ecart_prix);
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
    public function majCaisse($caisse , $etatPoids , $etatPrix, $log_action ,$codeArticle):void
    {
        $caisse->setLogAction($log_action);
        $caisse->setEtatPoids($etatPoids);
        $caisse->setEtatPrix($etatPrix);
        $caisse->setCode($codeArticle);
        $this->analyseRepo->saveCaisse($caisse);
    }
    
    public function getListe()
    {
        return $this->analyseRepo->getAllHisto();
    }
    public function getAnalyse($id)
    {
        return $this->analyseRepo->find($id);
    }

    
    public function getDetailsGlobal($id)
    {
        return $this->analyseRepo->getDetailsIllicar($id);
    }
    public function updateAnalyse($analyse , $prixCaisseSup ,$prixCaisseInf ,$poidsCaisseSup ,$poidsCaisseInf):void
    {
        $analyse->setPoidsCaisseInf($poidsCaisseInf);
        $analyse->setPoidsCaisseSup($poidsCaisseSup);
        $analyse->setPrixCaisseInf($prixCaisseInf);
        $analyse->setPrixCaisseSup($prixCaisseSup);
        $this->analyseRepo->saveAnalyse($analyse);
    }

    public function getPrixCaisseTotal( $id_analyse)
    {
        return $this->analyseRepo->getPrixCaisseTotal( $id_analyse);
    }

    public function getPoidsCaisseTotal( $id_analyse)
    {
        return $this->analyseRepo->getPoidsCaisseTotal( $id_analyse);
    }
    public function getPrixBalanceTotal( $id_analyse)
    {
        return $this->analyseRepo->getPrixBalanceTotal( $id_analyse);
    }

    public function getPoidsBalanceTotal( $id_analyse)
    {
        return $this->analyseRepo->getPoidsBalanceTotal( $id_analyse);
    }
}