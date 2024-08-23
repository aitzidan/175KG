<?php

namespace App\Controller\Tpe;

use App\Service\BaseService;
use App\Service\MessageService;
use App\Service\TpeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TpeController extends AbstractController
{
    private MessageService $MessageService;
    private BaseService $BaseService;
    private TpeService $TpeService;

    public function __construct(TpeService $TpeService, MessageService $MessageService, BaseService $BaseService)
    {
        $this->TpeService = $TpeService;
        $this->MessageService = $MessageService;
        $this->BaseService = $BaseService;
    }

    #[Route('/tpe/list', name: 'list_tpe')]
    public function index(Request $request): Response
    {
        $list = []; // Assuming this will be filled with your data
    
        $currentDate = new \DateTime();
        $currentDate->modify('first day of this month');
        $date_debut = $currentDate->format('Y-m-d');
        $currentDate->modify('last day of this month');
        $date_fin = $currentDate->format('Y-m-d');
        $filterType = 'date';

        $TPE = 0;
        $Espece = 0;
        $TPE_NAPS = 0; 
        $ECART = 0;

        // Get the current year and month
        $currentYear = (int)$currentDate->format('Y');
        $currentMonth = (int)$currentDate->format('n'); // Mois en tant que nombre (1-12)
    
        // Liste des mois de janvier jusqu'au mois actuel
        $listMonths = [];
        for ($month = 1; $month <= $currentMonth; $month++) {
            $listMonths[] = [
                'number' => $month,
                'name' =>  $this->BaseService->formatMonth($month)// Nom complet du mois
            ];
        }
        $listAllMonths = [];
        for ($month = 1; $month <=12 ; $month++) {
            $listAllMonths[] = [
                'number' => $month,
                'name' =>  $this->BaseService->formatMonth($month)// Nom complet du mois
            ];
        }
        $listYears = [];
        for ($year = 2020; $year <= $currentYear; $year++) {
            $listYears[] = $year;
        }
    
        if ($request->isMethod('POST')) {
            // Process form data here
            $filterType = $request->request->get('filter_type');
            $dateDebut = $request->request->get('date_debut');
            $dateFin = $request->request->get('date_fin');
            $annee = $request->request->get('annee');
            $mois = $request->request->get('mois');
            
            $list = $this->TpeService->getDataByFilter($filterType , $dateDebut , $dateFin , $annee , $mois);
            $TPE = $this->TpeService->getTpe($filterType , $dateDebut , $dateFin , $annee , $mois);
            $TPE_NAPS = $this->TpeService->getTpeNaps($filterType , $dateDebut , $dateFin , $annee , $mois);
            $ECART = $TPE - $TPE_NAPS;
            
        }else{
            $list = $this->TpeService->getTpeByDateRange($date_debut , $date_fin);
            $TPE_NAPS = $this->TpeService->getTpeSummaryByDateRange($date_debut , $date_fin) ?? 0;
            $TPE = $this->TpeService->getTpeNapsSummaryByDateRange($date_debut , $date_fin) ?? 0;

            $ECART = $TPE_NAPS - $TPE;
        }
    
        return $this->render('tpe/listTpe.html.twig', [
            'controller_name' => 'caisseController',
            'tpe' => $list,
            'date_debut' => $date_debut,
            'date_fin' => $date_fin,
            'list_years' => $listYears, 
            'list_months' => $listMonths ,
            'list_all_months' => $listAllMonths ,
            'currentYear' => $currentYear ,
            'filter_type'=>$filterType,
            'TPE'=>$TPE,
            'Espece'=>$Espece,
            'TPE_NAPS' =>$TPE_NAPS,
            'ECART' =>$ECART
        ]);
    }

    #[Route('/tpe/add', name: 'add_tpe')]
    public function addTpe(): Response
    {
        return $this->render('tpe/addTpe.html.twig', [
            'date' => new \DateTime('now')
        ]);
    }

    #[Route('/tpe/ajaxAddTpe', name: 'ajax_add_tpe')]
    public function ajaxAddTpe(Request $request): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $data = [
            'date' => $request->get('date'),
            'tpe_caisse' => $request->get('tpe_caisse'),
            'tpe_naps' => $request->get('tpe_naps'),
            'ecart' => $request->get('ecart')
        ];

        $checkData = $this->TpeService->checkData($data);

        if (!$checkData) {
            $codeStatut = 'ERREUR-PARAMS-VIDE';
        } else {
            $this->TpeService->addTpe($data);
            $codeStatut = "OK";
        }

        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/tpe/update/{id}', name: 'update_tpe')]
    public function updateTpe($id): Response
    {
        $tpe = $this->TpeService->getTpeById($id);

        return $this->render('tpe/updateTpe.html.twig', [
            'tpe' => $tpe,
            'id' => $id
        ]);
    }

    #[Route('/tpe/ajaxUpdateTpe/{id}', name: 'ajax_update_tpe')]
    public function ajaxUpdateTpe(Request $request, $id): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $tpe = $this->TpeService->getTpeById($id);
        $data = [
            'date' => $request->get('date'),
            'tpe_caisse' => $request->get('tpe_caisse'),
            'tpe_naps' => $request->get('tpe_naps'),
            'ecart' => $request->get('ecart')
        ];

        $checkData = $this->TpeService->checkData($data);
        if (!$checkData) {
            $codeStatut = 'ERREUR-PARAMS-VIDE';
        } else {
            $this->TpeService->saveTpe($data, $tpe);
            $codeStatut = "OK";
        }

        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    
    #[Route('/tpe/deleteTpe/{id}', name: 'ajax_delete_tpe')]
    public function ajaxDeleteCaisse(Request $request, $id): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $tpe = $this->TpeService->getTpeById($id);
        
        $this->TpeService->deleteTpe($tpe);

        $codeStatut = "OK";   
    
        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }



}
