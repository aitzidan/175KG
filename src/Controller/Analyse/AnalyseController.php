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

    #[Route('/analyse/checkFile' )]
    public function getColumnAction(SerializerInterface $serializer, Request $request)
    {
        $codeStatut = "ERROR";
        
        //TODO:Check caisse
        
        $filesCaisse = $request->files->get('file_caisse');
        $array_caisse = [`ticket`, `dates`, `heure`, `code`, `article`, `famille`, `prix_a`, `prix_mp`, `prix_v`, `qte`, `remise`, `total_net`, `caissier`, `vendeur`, `code_client`, `client`, `poste`, `operation`, `cloture_caissier`, `cloture_globale`, `notes`];

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
                        if(!in_array($col , $array_caisse)){
                            $respObjects["codeStatut"] = 'OK';
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

        //TODO:Check balance
        
        $filesBalance = $request->files->get('file_balance');
        $array_balance = [`nom`, `section`, `operations`, `poids`, `montant`];

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
                // Remove empty values
                $arrayFile = array_filter($arrayFile, function($value) {
                    return $value !== '';
                });
                // Re-index the array
                $arrayFile = array_values($arrayFile);

                if(count($arrayFile) != count($array_balance)){
                    $codeStatut = 'ERROR_FILE'.count($arrayFile).count($array_balance);
                    $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
                    return new JsonResponse($respObjects);
                }else{
                    
                    for ($i=0; $i < count($arrayFile); $i++) { 
                        $col = $result['colonnes'][$i];
                        $col = strtolower($col);
                        if(!in_array($col , $array_balance)){
                            $respObjects["codeStatut"] = 'OK';
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
        
        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return new JsonResponse($respObjects);
    }


}
