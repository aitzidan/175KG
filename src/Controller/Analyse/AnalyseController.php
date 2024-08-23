<?php

namespace App\Controller\Analyse;

use App\Service\AnalyseService;
use App\Service\BaseService;
use App\Service\MessageService;
use Spipu\Html2Pdf\Html2Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class AnalyseController extends AbstractController
{
    private MessageService $MessageService;
    private BaseService $BaseService;
    private AnalyseService $AnalyseService;

    public function __construct(MessageService $MessageService,BaseService $BaseService , AnalyseService $AnalyseService)
    {
        $this->MessageService = $MessageService;
        $this->BaseService = $BaseService;
        $this->AnalyseService = $AnalyseService;
    }
    #[Route('/analyse', name: 'app_analyse_analyse')]
    public function index(): Response
    {
        $chckAccess = $this->BaseService->Role(10);
        if($chckAccess == 0){
            return $this->redirectToRoute('login');
        }else if ($chckAccess == 2){
            return $this->redirectToRoute('listUsers');
        }

        $codeStatut = "";
        $response = "";

        $typeFichier = $this->AnalyseService->getTypeFichier();
        
        return $this->render('analyse/index.html.twig', [
            'controller_name' => 'AnalyseController',
            'response' => $response,
            'codeStatut' => $codeStatut,
            'typeFichier' => $typeFichier,
        ]);
    }

    #[Route('/analyse/checkFile' , methods:['POST'] )]
    public function getColumnAction(SerializerInterface $serializer, Request $request):JsonResponse
    {
        $codeStatut = "ERROR";
        //TODO:Check caisse
        $titre = $request->get('titre');
        $date_debut = $request->get('date_debut');
        $date_fin = $request->get('date_fin');

        if($titre != "" && !empty($date_debut) && !empty($date_fin) ){
            $date_debut = new \DateTime($date_debut);
            $date_fin = new \DateTime($date_fin);

            if(! ($date_debut > $date_fin)){

                $filesCaisse = $request->files->get('file_caisse');
                $array_caisse = ['ticket', 'dates', 'heure', 'code', 'article', 'famille', 'prix_a', 'prix_mp', 'prix_v', 'qte', 'remise', 'total_net', 'caissier', 'vendeur', 'code_client', 'client', 'poste', 'operation', 'cloture_caissier', 'cloture_globale', 'notes'];
        
                foreach ($filesCaisse as $file) {
                    $fileArray = [
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'error' => $file->getError(),
                        'tmp_name' => $file->getPathname()
                    ];
                    $result = $this->BaseService->getColumnsFile($fileArray, $serializer);
        
                    if($result['response'] == 'success'){
                        $arrayFile = json_decode($result['colonnes'] , true);
                        // Remove empty values
                        $arrayFile = array_filter($arrayFile, function($value) {
                            return $value !== '';
                        });
                        // Re-index the array
                        $arrayFile = array_values($arrayFile);
        
                        if(count($arrayFile) != count($array_caisse)){
                            $codeStatut = 'ERROR_FILE';
                            $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
                            return new JsonResponse($respObjects);
                        }else{
                            
                            for ($j=0; $j < count($arrayFile); $j++) { 
                                $col = $result['colonnes'][$j];
                                $col = strtolower($col);
                                if(in_array($col, $array_caisse)){
                                    $codeStatut='OK';
                                    $respObjects["message"] = $this->MessageService->checkMessage('OK');
                                }
                            }
                        }
                    }else{
                        $respObjects["codeStatut"] = 'ERROR';
                        $respObjects["message"] = $result['response'];
                        return new JsonResponse($respObjects);
                    }
                }
                //TODO: Check balance
                
                $filesBalance = $request->files->get('file_balance');
                $array_balance = ['nom', 'section', 'operations', 'poids', 'montant']; // Use single quotes
                
                foreach ($filesBalance as $file) {
                    $fileArray = [
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'error' => $file->getError(),
                        'tmp_name' => $file->getPathname()
                    ];
                    $result = $this->BaseService->getColumnsFile($fileArray, $serializer);
                    if($result['response'] == 'success'){
                        
                        $arrayFile = json_decode($result['colonnes'] , true);
                        $arrayFile = [$arrayFile[0] , $arrayFile[6] , $arrayFile[9], $arrayFile[11], $arrayFile[13]];
                        
                        // Remove empty values
                        $arrayFile = array_filter($arrayFile, function($value) {
                            return $value !== '';
                        });
                        // Re-index the array
                        $arrayFile = array_values($arrayFile);
        
                        if(count($arrayFile) != count($array_balance)){
                            $codeStatut = 'ERROR_FILE';
                            $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
                            return new JsonResponse($respObjects);
                        } else {
                            for ($j = 0; $j < count($arrayFile); $j++) { 
                                $col = $arrayFile[$j];
                                $col = strtolower($col);
                                
                                if(in_array($col, $array_balance)){
                                    $codeStatut='OK';
                                    $respObjects["message"] = $this->MessageService->checkMessage('OK');
                                }else{
                                    $respObjects["codeStatut"] = 'ERROR_ENTETE';
                                    $respObjects["message"] = $this->MessageService->checkMessage('ERROR_ENTETE');
                                    return new JsonResponse($respObjects);
                                }
                            }
                        }
                    } else {
                        $respObjects["codeStatut"] = 'ERROR';
                        $respObjects["message"] = $result['response'];
                        return new JsonResponse($respObjects);
                    }
                }
        
                if($codeStatut == "OK"){
                    $analyse = $this->AnalyseService->addAnalyse($titre , $date_debut , $date_fin);
        
                    $filesCaisse = $request->files->get('file_caisse');
                    foreach ($filesCaisse as $file) {
                        # code...
                        
                        $uploadDir = 'fichier'; // Ensure this directory exists and is writable
                        $uploadResponse = $this->BaseService->uploadFileAnalyseCaisse($file, $uploadDir);
                        $fichier = $this->AnalyseService->addFichier( $analyse , 2 ,$uploadResponse['url']);
                        $upload  = $this->AnalyseService->sauvguardeDataCSV($fichier->getId() ,2 ,$analyse->getId());

                        if($upload != "OK"){
                            $codeStatut = "ERROR_UPLOAD_DATA";
                            $respObjects["codeStatut"] = $codeStatut;
                            $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
                            return new JsonResponse($respObjects);
                        }
                    }

                    foreach ($filesBalance as $file) {
                        # code...
                        $uploadDir = 'fichier'; // Ensure this directory exists and is writable
                        $uploadResponse = $this->BaseService->uploadFileAnalyse($file, $uploadDir);
                        $fichier = $this->AnalyseService->addFichier( $analyse , 1 ,$uploadResponse['url']);

                        $upload  =$this->AnalyseService->sauvguardeDataExcel($fichier->getId() , 1 , $analyse->getId());
                        
                        if($upload != "OK"){
                            $codeStatut = "ERROR_UPLOAD_DATA";
                            $respObjects["codeStatut"] = $codeStatut;
                            $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
                            return new JsonResponse($respObjects);
                        }
                    }
                    $id_analyse = $analyse->getId();
                    $respObjects['analyse'] = $id_analyse;

                    $balance = $this->AnalyseService->getBalanceByAnalyse($id_analyse);

                    $prixCaisseSup = 0;
                    $prixCaisseInf = 0;

                    $poidsCaisseSup = 0;
                    $poidsCaisseInf = 0;


                    for ($i=0; $i < count($balance); $i++) { 
                        $log_action = "";
                        $poids = $balance[$i]->getPoids();
                        $nom = $balance[$i]->getNom();
                        $prix  = $balance[$i]->getMontant();
                        $codeArticle = '';
                        //TODO:Cheking the qte of balance
    
                        if (preg_match('/\d+/', $nom, $matches)) {
                            $codeArticle = $matches[0];
                        }
    
                        $poidsInCaisse = $this->AnalyseService->getPoidsCaisse( $codeArticle , $id_analyse );
                        $etatPoids = 2;
                        $ecart_poids = $poids - $poidsInCaisse;
                        
                        if ($poids > $poidsInCaisse) {
                            $log_action .= '- Le poids de la balance est supérieur au poids en caisse.<br/>';
                            $poidsCaisseSup += ($poids > $poidsInCaisse);
                        } else if ($poids < $poidsInCaisse) {
                            $log_action .= '- Le poids de la balance est inférieur au poids en caisse.<br/>';
                            $poidsCaisseInf += ($poids < $poidsInCaisse);
                        } else if ($poids == $poidsInCaisse) {
                            $log_action .= '- OK (Poids).<br/>';
                            $etatPoids = 1;
                        }
                        
                        // Checking the price of balance
                        $prixInCaisse = $this->AnalyseService->getPrixCaisse($codeArticle, $id_analyse);
                        $etatPrix = 2;

                        $ecart_prix = $prix - $prixInCaisse;
                        
                        if ($prix > $prixInCaisse) {
                            $log_action .= '- Le prix de la balance est supérieur au prix en caisse.<br/>';
                            $prixCaisseSup += ($prix > $prixInCaisse);
                        } else if ($prix < $prixInCaisse) {
                            $log_action .= '- Le prix de la balance est inférieur au prix en caisse.<br/>';
                            $prixCaisseInf += ($prix < $prixInCaisse);
                        } else if ($prix == $prixInCaisse) {
                            $log_action .= '- OK (Prix).<br/>';
                            $etatPrix = 1;
                        }
                        
                        $this->AnalyseService->majBalance( $balance[$i] , $etatPoids , $etatPrix , $log_action ,$codeArticle , $ecart_poids, $ecart_prix);
                    }
    
                    $caisse = $this->AnalyseService->getCaisseByAnalyse($id_analyse);
                    for ($j=0; $j < count($caisse); $j++) { 
                        $log_action = "";
                        
                        //TODO: Data Balance
                        $qte = $caisse[$j]->getQte();
                        $prixV = $caisse[$j]->getPrixV();
                        $totalNet  = $caisse[$j]->getTotalNet();
                        $code  = $caisse[$j]->getCode();
                        
                        $etatPoids = 2;
                        $etatPrix = 2;
                        
                        //TODO: Data caisse
                        
                        $balance = $this->AnalyseService->getBalanceByCode($code , $id_analyse);
                        if($balance){
    
                            $montant = $balance->getMontant();
                            $poids = $balance->getPoids();
        
                            //TODO: Check prix
                            $checkPrix = ($montant) / $poids;
                            
                            if( ($prixV - 1) <=  $checkPrix && $checkPrix <= ($prixV + 1)) {
                                $log_action .= '- OK (Prix).<br/>';
                                $etatPrix = 1;
                            } else if ($checkPrix < $prixV) {
                                $log_action .= '- Le prix de caisse est inférieur au montant en balance.<br/>';
                            } else if ($checkPrix > $prixV) {
                                $log_action .= '- Le prix de caisse est supérieur au montant en balance.<br/>';
                            }
        
                            //TODO: Check poids
                            $qteCheck = ($totalNet * $poids) / $montant;
                            
                            if(  $qteCheck == $qte) {
                                $log_action .= '- OK (Quantité).<br/>';
                                $etatPoids = 1;
                            } else if ($qteCheck < $qte) {
                                $log_action .= '- La quantité de caisse est inférieur au poids en balance.<br/>';
                            } else if ($qteCheck > $qte) {
                                $log_action .= '- La quantité de caisse est supérieur au montant en balance.<br/>';
                            }
                            $this->AnalyseService->majCaisse( $caisse[$j] , $etatPoids , $etatPrix , $log_action ,$codeArticle );
                        }
                    }
                    
                    $poidsCaisseTotal = $this->AnalyseService->getPoidsCaisseTotal($id_analyse);
                    $prixCaisseTotal = $this->AnalyseService->getPrixCaisseTotal($id_analyse);
                    $prixBalanceTotal = $this->AnalyseService->getPrixBalanceTotal($id_analyse);
                    $poidsBalanceTotal = $this->AnalyseService->getPoidsBalanceTotal($id_analyse);

                    $p = $poidsBalanceTotal - $poidsCaisseTotal;
                    $c = $prixBalanceTotal - $prixCaisseTotal;


                    //TODO:Update analyse
                    $this->AnalyseService->updateAnalyse($analyse ,$c ,$prixCaisseInf , $p ,$poidsCaisseInf);



                    $listBalance = $this->AnalyseService->getBalanceByAnalyse($id_analyse);
                    $caisse = $this->AnalyseService->getCaisseByAnalyse($id_analyse);
                    $analyse = $this->AnalyseService->getAnalyse($id_analyse);

                    // $balance = array();
                    $balance = [];
                    for ($i = 0; $i < count($listBalance); $i++) { 
                                    
                        

                    }
                    $j = 0;
                    foreach ($listBalance as $b) {
                        $isEcartZero = ($b->getEcartPoids() == 0) && ($b->getEcartPrix() == 0);
                        if (!$isEcartZero) {
                            $nom = $b->getNom();
                            $balance[$j]['Article'] = isset($nom) ? explode('-', $nom)[1] : '';
                            $balance[$j]['Poids'] = $b->getPoids();
                            $balance[$j]['Montant'] = $b->getMontant();
    
                            $Qte = $this->AnalyseService->getPoidsCaisse( $b->getCode() , $id_analyse );
                            $balance[$j]['Qte'] = $Qte ?? '--';;
    
                            $totalNet = $this->AnalyseService->getPrixCaisse( $b->getCode() , $id_analyse );
                            $balance[$j]['TOTAL_NET'] = $totalNet ?? '--';
    
                            $balance[$j]['ECART'] = $b->getEcartPoids();
    
                            $balance[$j]['ECART_MAD'] = $b->getEcartPrix();
                            $j++;
                        }
                    }

                    $respObjects['analyse'] = $analyse;
                    $respObjects['balance'] = $balance;
                    $respObjects['caisse'] = $caisse;

                    $codeStatut="OK"; 
                }
                
            }else{
                $codeStatut = "ERROR_DATE";
            }
        }else{
            $codeStatut = "ERREUR-PARAMS-VIDE";
        }

        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }
    #[Route('/analyse/historique', name: 'app_analyse_historique')]
    public function historique(): Response
    {
        $codeStatut = "";
        $response = "";

        $analyse = $this->AnalyseService->getListe();
        
        return $this->render('analyse/historique.html.twig', [
            'controller_name' => 'AnalyseController',
            'response' => $response,
            'codeStatut' => $codeStatut,
            'analyse' => $analyse,
        ]);
    }

    #[Route('/analyse/historique/{id}/', name: 'app_analyse_historique_details')]
    public function historiqueDetails($id): Response
    {

        $analyse = $this->AnalyseService->getAnalyse($id);

        // $detailsGlobal = $this->AnalyseService->getDetailsGlobal($id);
        
        return $this->render('analyse/details.html.twig', [
            'controller_name' => 'AnalyseController',
            'analyse' => $analyse,
            'id' => $id,
        ]);
    }


    #[Route('/analyse/getDatils/{id}')]
    public function getHistoriqueDetails($id): Response
    {
        $this->BaseService->RoleJson(11);
        
        $balance = $this->AnalyseService->getBalanceByAnalyse($id);
        $caisse = $this->AnalyseService->getCaisseByAnalyse($id);
        $respObjects['balance'] = $balance;
        $respObjects['caisse'] = $caisse;
        $codeStatut = "OK";

        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);

        return $this->json($respObjects);
    }

    
    #[Route('/pdfAnalyse/{id}/', name: 'pdfAnalyse')]
    public function pdfDevis( $id)
    {
        
        $analyse = $this->AnalyseService->getAnalyse($id);

        $listBalance = $this->AnalyseService->getBalanceByAnalyse($id);

        // $balance = array();
        $balance = [];
        for ($i = 0; $i < count($listBalance); $i++) { 

            $isEcartZero = ($listBalance[$i]->getEcartPoids() == 0) && ($listBalance[$i]->getEcartPrix() == 0);
            if (!$isEcartZero) {
                $nom = $listBalance[$i]->getNom();
                $balance[$i]['Article'] = isset($nom) ? explode('-', $nom)[1] : '';
                $balance[$i]['Poids'] = $listBalance[$i]->getPoids();
                $balance[$i]['Montant'] = $listBalance[$i]->getMontant();
        
                $Qte = $this->AnalyseService->getPoidsCaisse($listBalance[$i]->getCode(), $id);
                $balance[$i]['Qte'] = $Qte ?? '--';
        
                $totalNet = $this->AnalyseService->getPrixCaisse($listBalance[$i]->getCode(), $id);
                $balance[$i]['TOTAL_NET'] = $totalNet ?? '--';
        
                $balance[$i]['ECART'] = $listBalance[$i]->getEcartPoids();
                $balance[$i]['ECART_MAD'] = $listBalance[$i]->getEcartPrix();
            }
        }
        
        // Render the view
        $htmlContent = $this->renderView('analyse/analysePdf.html.twig', [
          "analyse"=>$analyse,
          "balance"=>$balance   
        ]);
        $html2pdf = new Html2Pdf();
        $html2pdf->writeHTML($htmlContent);
        $pdfOutput = $html2pdf->output();
        $response = new Response($pdfOutput);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'inline; filename=bon_commande.pdf');

        return $response;

    }

    #[Route('/excelAnalyse/{id}/', name: 'excelAnalyse')]
    public function excelAnalyse($id)
    {
        // Start output buffering
        ob_start();
    
        // Fetch data
        $analyse = $this->AnalyseService->getAnalyse($id);
        $listBalance = $this->AnalyseService->getBalanceByAnalyse($id);
    
        $balance = [];
        foreach ($listBalance as $i => $item) {
            $nom = $item->getNom();
            $balance[$i] = [
                'Article' => isset($nom) ? explode('-', $nom)[1] : '',
                'Poids' => $this->convertToNumber($item->getPoids()),
                'Montant' => $this->convertToNumber($item->getMontant()),
                'Qte' => $this->convertToNumber($this->AnalyseService->getPoidsCaisse($item->getCode(), $id)),
                'TOTAL_NET' => $this->convertToNumber($this->AnalyseService->getPrixCaisse($item->getCode(), $id)),
                'ECART' => $this->convertToNumber($item->getEcartPoids()),
                'ECART_MAD' => $this->convertToNumber($item->getEcartPrix())
            ];
        }
    
        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Analyse');
    
        // Set header
        $headers = ['Article', 'Poids', 'Montant', 'Qte', 'TOTAL_NET', 'ECART', 'ECART_MAD'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }
    
        // Add data
        $row = 2; // Start from row 2
        foreach ($balance as $item) {
            $sheet->setCellValue('A' . $row, $item['Article']);
            $sheet->setCellValue('B' . $row, $item['Poids']);
            $sheet->setCellValue('C' . $row, $item['Montant']);
            $sheet->setCellValue('D' . $row, $item['Qte']);
            $sheet->setCellValue('E' . $row, $item['TOTAL_NET']);
            $sheet->setCellValue('F' . $row, $item['ECART']);
            $sheet->setCellValue('G' . $row, $item['ECART_MAD']);
            $row++;
        }
    
        // Create Excel writer
        $writer = new Xlsx($spreadsheet);
        $fileName = 'analyse_data.xlsx';
    
        // Create response
        $response = new Response();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->headers->set('Cache-Control', 'max-age=0');
    
        // Save the file to output
        $writer->save('php://output');
    
        // End buffering and get contents
        $content = ob_get_clean();
    
        // Send the response
        $response->setContent($content);
        return $response;
    }
    
    private function convertToNumber($value)
    {
        // Convert to float if numeric or default to 0.0
        return is_numeric($value) ? (float)$value : 0.0;
    }
    
        

}
