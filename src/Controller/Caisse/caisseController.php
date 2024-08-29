<?php

namespace App\Controller\Caisse;

use App\Service\BaseService;
use App\Service\CaisseParamService;
use App\Service\CaisseService;
use App\Service\MessageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class caisseController extends AbstractController
{
    private MessageService $MessageService;
    private BaseService $BaseService;
    private CaisseService $CaisseService;
    private CaisseParamService $CaisseParamService;

    public function __construct(MessageService $MessageService,BaseService $BaseService , CaisseService $CaisseService,CaisseParamService $CaisseParamService)
    {
        $this->MessageService = $MessageService;
        $this->BaseService = $BaseService;
        $this->CaisseParamService = $CaisseParamService;
        $this->CaisseService = $CaisseService;
    }

    #[Route('/caisse/list', name: 'list_caisse')]
    public function index(Request $request): Response
    {
        $list = []; // Assuming this will be filled with your data
        
        $chckAccess = $this->BaseService->Role(78);
        if($chckAccess == 0){
            return $this->redirectToRoute('login');
        }else if ($chckAccess == 2){
            return $this->redirectToRoute('listUsers');
        }
    
        $currentDate = new \DateTime();
        $currentDate->modify('first day of this month');
        $date_debut = $currentDate->format('Y-m-d');
        $currentDate->modify('last day of this month');
        $date_fin = $currentDate->format('Y-m-d');
        $filterType = 'date';
        $TPE = 0;
        $Espece = 0;
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
            
            $list = $this->CaisseService->getDataByFilter($filterType , $dateDebut , $dateFin , $annee , $mois);
            $Espece = $this->CaisseService->getTheEspece($filterType , $dateDebut , $dateFin , $annee , $mois);
            $TPE = $this->CaisseService->getTpe($filterType , $dateDebut , $dateFin , $annee , $mois);
            $ECART = $this->CaisseService->getEcart($filterType , $dateDebut , $dateFin , $annee , $mois);

        }else{
            $list = $this->CaisseService->getDataByFilter2($date_debut , $date_fin);
            $Espece = $this->CaisseService->getTheEspece2($date_debut , $date_fin) ?? 0;
            $TPE = $this->CaisseService->getTpe2($date_debut , $date_fin) ?? 0;
            $ECART = $this->CaisseService->getEcart2($date_debut , $date_fin) ?? 0;
        }
    
        return $this->render('caisse/listCaisse.html.twig', [
            'controller_name' => 'caisseController',
            'caisse' => $list,
            'date_debut' => $date_debut,
            'date_fin' => $date_fin,
            'list_years' => $listYears, 
            'list_months' => $listMonths ,
            'list_all_months' => $listAllMonths ,
            'currentYear' => $currentYear ,
            'filter_type'=>$filterType,
            'TPE'=>$TPE,
            'Espece'=>$Espece,
            'ECART'=>$ECART,
        ]);
    }

    #[Route('/caisse/add', name: 'add_caisse')]
    public function addCaisse(): Response
    {
        $chckAccess = $this->BaseService->Role(74);
        if($chckAccess == 0){
            return $this->redirectToRoute('login');
        }else if ($chckAccess == 2){
            return $this->redirectToRoute('listUsers');
        }

        $listParam = $this->CaisseParamService->getListCaisse();
        return $this->render('caisse/addCaisse.html.twig', [
           'date'=>new \DateTime('now'),
           'listParam'=>$listParam
        ]);
    }

    #[Route('/caisse/ajaxAddCaisse', name: 'ajax_add_caisse')]
    public function ajaxAddCaisse(Request $request): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $chckAccess = $this->BaseService->Role(74);
        if($chckAccess == 0){
            return $this->json($this->BaseService->errorAccess());
        }else if ($chckAccess == 2){
            return $this->json($this->BaseService->errorAccess());
        }

        $data = [
            'date' => $request->get('date'),
            'tpe' => $request->get('tpe'),
            'espece' => $request->get('espece'),
            'amount' => $request->get('amount'),
            'espece_final' => $request->get('espece_final'),
            'tpe_naps' => $request->get('tpe_naps'),
            'ecart' => $request->get('ecart'),
            'caisse' => $request->get('caisse'),
        ];
        $checkData = $this->CaisseService->checkData($data);

        if (!$checkData) {
            $codeStatut = 'ERREUR-PARAMS-VIDE';
        } else {
            $checkData = $this->CaisseService->addCaisse($data);
            $codeStatut = "OK";
        }
        
       
        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/caisse/update/{id}', name: 'update_caisse')]
    public function updateCaisse($id): Response
    {
        $chckAccess = $this->BaseService->Role(75);
        if($chckAccess == 0){
            return $this->redirectToRoute('login');
        }else if ($chckAccess == 2){
            return $this->redirectToRoute('listUsers');
        }

        $caisse = $this->CaisseService->getCaisse($id);
        $listParam = $this->CaisseParamService->getListCaisse();

        return $this->render('/caisse/updateCaisse.html.twig', [
           'caisse'=>$caisse,
           'id'=>$id,
           'listParam'=>$listParam
        ]);
    }
    
    #[Route('/caisse/ajaxUpdateCaisse/{id}', name: 'ajax_update_caisse')]
    public function ajaxUpdateCaisse(Request $request, $id): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $chckAccess = $this->BaseService->Role(75);
        if($chckAccess == 0){
            return $this->json($this->BaseService->errorAccess());
        }else if ($chckAccess == 2){
            return $this->json($this->BaseService->errorAccess());
        }


        $caisse = $this->CaisseService->getCaisse($id);
        $data = [
            'date' => $request->get('date'),
            'tpe' => $request->get('tpe'),
            'espece' => $request->get('espece'),
            'amount' => $request->get('amount'),
            'espece_final' => $request->get('espece_final'),
            'tpe_naps' => $request->get('tpe_naps'),
            'ecart' => $request->get('ecart'),
            'caisse' => $request->get('caisse'),
        ];

        $checkData = $this->CaisseService->checkData($data);
        if (!$checkData) {
            $codeStatut = 'ERREUR-PARAMS-VIDE';
        } else {
            $checkData = $this->CaisseService->saveCaisse($data, $caisse);
            $codeStatut = "OK";
        }
    
        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/caisse/deleteCaisse/{id}', name: 'ajax_delete_caisse')]
    public function ajaxDeleteCaisse(Request $request, $id): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $chckAccess = $this->BaseService->Role(76);
        if($chckAccess == 0){
            return $this->json($this->BaseService->errorAccess());
        }else if ($chckAccess == 2){
            return $this->json($this->BaseService->errorAccess());
        }


        $caisse = $this->CaisseService->getCaisse($id);
        
        $this->CaisseService->deleteCaisse($caisse);

        $codeStatut = "OK";   
    
        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/caisse/validate/{id}', name: 'ajax_validate_caisse')]
    public function ajaxValidateCaisse(Request $request, $id): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $chckAccess = $this->BaseService->Role(77);
        if($chckAccess == 0){
            return $this->json($this->BaseService->errorAccess());
        }else if ($chckAccess == 2){
            return $this->json($this->BaseService->errorAccess());
        }

        $caisse = $this->CaisseService->getCaisse($id);
        
        $this->CaisseService->validateCaisse($caisse);
        $codeStatut = "OK";   
    
        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }




    
}
