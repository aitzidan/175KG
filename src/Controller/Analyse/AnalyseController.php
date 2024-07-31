<?php

namespace App\Controller\Analyse;

use App\Service\AnalyseService;
use App\Service\BaseService;
use App\Service\MessageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

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
        
                // foreach ($filesCaisse as $file) {
                //     $fileArray = [
                //         'name' => $file->getClientOriginalName(),
                //         'size' => $file->getSize(),
                //         'error' => $file->getError(),
                //         'tmp_name' => $file->getPathname()
                //     ];
                //     $result = $this->BaseService->getColumnsFile($fileArray, $serializer);
        
                //     if($result['response'] == 'success'){
                //         $arrayFile = json_decode($result['colonnes'] , true);
                //         // Remove empty values
                //         $arrayFile = array_filter($arrayFile, function($value) {
                //             return $value !== '';
                //         });
                //         // Re-index the array
                //         $arrayFile = array_values($arrayFile);
        
                //         if(count($arrayFile) != count($array_caisse)){
                //             $codeStatut = 'ERROR_FILE';
                //             $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
                //             return new JsonResponse($respObjects);
                //         }else{
                            
                //             for ($j=0; $j < count($arrayFile); $j++) { 
                //                 $col = $result['colonnes'][$j];
                //                 $col = strtolower($col);
                //                 if(in_array($col, $array_caisse)){
                //                     $codeStatut='OK';
                //                     $respObjects["message"] = $this->MessageService->checkMessage('OK');
                //                 }
                //             }
                //         }
                //     }else{
                //         $respObjects["codeStatut"] = 'ERROR';
                //         $respObjects["message"] = $result['response'];
                //         return new JsonResponse($respObjects);
                //     }
                // }
                // //TODO: Check balance
                
                // $filesBalance = $request->files->get('file_balance');
                // $array_balance = ['nom', 'section', 'operations', 'poids', 'montant']; // Use single quotes
        
                // foreach ($filesBalance as $file) {
                //     $fileArray = [
                //         'name' => $file->getClientOriginalName(),
                //         'size' => $file->getSize(),
                //         'error' => $file->getError(),
                //         'tmp_name' => $file->getPathname()
                //     ];
                //     $result = $this->BaseService->getColumnsFile($fileArray, $serializer);
                    
                //     if($result['response'] == 'success'){
                //         $arrayFile = json_decode($result['colonnes'] , true);
                //         // Remove empty values
                //         $arrayFile = array_filter($arrayFile, function($value) {
                //             return $value !== '';
                //         });
                //         // Re-index the array
                //         $arrayFile = array_values($arrayFile);
        
                //         if(count($arrayFile) != count($array_balance)){
                //             $codeStatut = 'ERROR_FILE'.count($arrayFile);
                //             $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
                //             return new JsonResponse($respObjects);
                //         } else {
                //             for ($j = 0; $j < count($arrayFile); $j++) { 
                //                 $col = $arrayFile[$j];
                //                 $col = strtolower($col);
                //                 if(in_array($col, $array_balance)){
                //                     $codeStatut='OK';
                //                     $respObjects["message"] = $this->MessageService->checkMessage('OK');
                //                 }else{
                //                     $respObjects["codeStatut"] = 'ERROR_ENTETE';
                //                     $respObjects["message"] = $this->MessageService->checkMessage('ERROR_ENTETE');
                //                     return new JsonResponse($respObjects);
                //                 }
                //             }
                //         }
                //     } else {
                //         $respObjects["codeStatut"] = 'ERROR';
                //         $respObjects["message"] = $result['response'];
                //         return new JsonResponse($respObjects);
                //     }
                // }
        
                // if($codeStatut == "OK"){
                //     $analyse = $this->AnalyseService->addAnalyse($titre , $date_debut , $date_fin);
        
                //     $filesCaisse = $request->files->get('file_caisse');
                //     foreach ($filesCaisse as $file) {
                //         # code...
                        
                //         $uploadDir = 'fichier'; // Ensure this directory exists and is writable
                //         $uploadResponse = $this->BaseService->uploadFileAnalyse($file, $uploadDir);
                //         $fichier = $this->AnalyseService->addFichier( $analyse , 2 ,$uploadResponse['url']);
                //         $upload  =$this->AnalyseService->sauvguardeDataCSV($fichier->getId() ,2 ,$analyse->getId());

                //         if($upload != "OK"){
                //             $codeStatut = "ERROR_UPLOAD_DATA";
                //             $respObjects["codeStatut"] = $codeStatut;
                //             $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
                //             return new JsonResponse($respObjects);
                //         }
                //     }

                //     foreach ($filesBalance as $file) {
                //         # code...

                //         $uploadDir = 'fichier'; // Ensure this directory exists and is writable
                //         $uploadResponse = $this->BaseService->uploadFileAnalyse($file, $uploadDir);
                //         $fichier = $this->AnalyseService->addFichier( $analyse , 1 ,$uploadResponse['url']);

                //         $upload  =$this->AnalyseService->sauvguardeDataCSV($fichier->getId() , 1 , $analyse->getId());
                        
                //         if($upload != "OK"){
                //             $codeStatut = "ERROR_UPLOAD_DATA";
                //             $respObjects["codeStatut"] = $codeStatut;
                //             $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
                //             return new JsonResponse($respObjects);
                //         }
                //     }
                // }

                $id_analyse = 48;
                $balance = $this->AnalyseService->getBalanceByAnalyse($id_analyse);
                for ($i=0; $i < count($balance); $i++) { 
                    $log_action = "";
                    $poids = $balance[$i]->getPoids();
                    $nom = $balance[$i]->getNom();
                    $prix  = $balance[$i]->getMontant();
                    $codeArticle = '';

                    //TODO:Cheking the qte of balance

                    if (preg_match('/^\d+/', $nom, $matches)) {
                        $codeArticle = $matches[0];
                    }

                    $poidsInCaisse = $this->AnalyseService->getPoidsCaisse( $codeArticle , $id_analyse );
                    $etatPoids = 2;
                    
                    if ($poids > $poidsInCaisse) {
                        $log_action .= '- Le poids de la balance est supérieur au poids en caisse.<br/>';
                    } else if ($poids < $poidsInCaisse) {
                        $log_action .= '- Le poids de la balance est inférieur au poids en caisse.<br/>';
                    } else if ($poids == $poidsInCaisse) {
                        $log_action .= '- OK (Poids).<br/>';
                        $etatPoids = 1;
                    }
                    
                    // Checking the price of balance
                    $prixInCaisse = $this->AnalyseService->getPrixCaisse($codeArticle, $id_analyse);
                    $etatPrix = 2;
                    
                    if ($prix > $prixInCaisse) {
                        $log_action .= '- Le prix de la balance est supérieur au prix en caisse.<br/>';
                    } else if ($prix < $prixInCaisse) {
                        $log_action .= '- Le prix de la balance est inférieur au prix en caisse.<br/>';
                    } else if ($prix == $prixInCaisse) {
                        $log_action .= '- OK (Prix).<br/>';
                        $etatPrix = 1;
                    }

                    $this->AnalyseService->majBalance( $balance[$i] , $etatPoids , $etatPrix , $log_action ,$codeArticle );
                }

                $caisse = $this->AnalyseService->getCaisseByAnalyse($id_analyse);
                for ($j=0; $j < count($caisse); $j++) { 
                    $log_action = "";

                    //TODO: Data Balance
                    $qte = $caisse[$j]->getQte();
                    $prixV  = $caisse[$i]->getPrixV();
                    $totalNet  = $caisse[$i]->getTotalNet();
                    $code  = $caisse[$i]->getCode();

                    //TODO: Data caisse

                    $balance = $this->AnalyseService->getBalanceByCode($code , $id_analyse);
                    $montant = $balance->getMontant();
                    $poids = $balance->getPoids();

                    //TODO: Check entite
                    $checkPrix = ($montant) / $poids;
                    
                    


                }

                

                $codeStatut="OK"; 
                
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


}
