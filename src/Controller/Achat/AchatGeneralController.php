<?php

namespace App\Controller\Achat;

use App\Service\BaseService;
use App\Service\MessageService;
use App\Service\AchatGeneralService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AchatGeneralController extends AbstractController
{
    private MessageService $MessageService;
    private BaseService $BaseService;
    private AchatGeneralService $AchatGeneralService;

    public function __construct(AchatGeneralService $AchatGeneralService, MessageService $MessageService, BaseService $BaseService)
    {
        $this->AchatGeneralService = $AchatGeneralService;
        $this->MessageService = $MessageService;
        $this->BaseService = $BaseService;
    }

    #[Route('/achat-general/add', name: 'add_achat_general')]
    public function addAchatGeneral(): Response
    {
        
        $category = $this->AchatGeneralService->getCategorie();
        $fournisseurs = $this->AchatGeneralService->getFournisseurs();

        $designation = $this->AchatGeneralService->getDesignation();

        return $this->render('achat/addAchatGeneral.html.twig', [
            'date' => new \DateTime('now'),
            'categories' => $category,
            'fournisseurs' => $fournisseurs,
            'designation' => $designation
        ]);
    }

    #[Route('/achat-general/ajaxAddAchatGeneral', name: 'ajax_add_achat_general')]
    public function ajaxAddAchatGeneral(Request $request): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $data = [
            'date' => $request->get('date'),
            'categorie' => $request->get('categorie'),
            'designation' => $request->get('designation'),
            'fournisseur' => $request->get('fournisseur'),
            'unite' => $request->get('unite'),
            'qte' => $request->get('qte'),
            'prix' => $request->get('prix'),
            'montant' => $request->get('montant'),
        ];

        $checkData = $this->AchatGeneralService->checkData($data);

        if (!$checkData) {
            $codeStatut = 'ERREUR-PARAMS-VIDE';
        } else {
            $this->AchatGeneralService->addAchatGeneral($data);
            $codeStatut = "OK";
        }

        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/achat-general/update/{id}', name: 'update_achat_general')]
    public function updateAchatGeneral($id): Response
    {
        $achatGeneral = $this->AchatGeneralService->getAchatGeneralById($id);
        $category = $this->AchatGeneralService->getCategorie();
        $fournisseurs = $this->AchatGeneralService->getFournisseurs();
        $designation = $this->AchatGeneralService->getDesignation();

        return $this->render('achat/updateAchatGeneral.html.twig', [
            'achatGeneral' => $achatGeneral,
            'id' => $id,
            'categories' => $category,
            'fournisseurs' => $fournisseurs,
            'designation' => $designation
        ]);
    }

    #[Route('/achat-general/ajaxUpdateAchatGeneral/{id}', name: 'ajax_update_achat_general')]
    public function ajaxUpdateAchatGeneral(Request $request, $id): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $achatGeneral = $this->AchatGeneralService->getAchatGeneralById($id);
        $data = [
            'date' => $request->get('date'),
            'categorie' => $request->get('categorie'),
            'designation' => $request->get('designation'),
            'fournisseur' => $request->get('fournisseur'),
            'unite' => $request->get('unite'),
            'qte' => $request->get('qte'),
            'prix' => $request->get('prix'),
            'montant' => $request->get('montant'),
        ];

        $checkData = $this->AchatGeneralService->checkData($data);
        if (!$checkData) {
            $codeStatut = 'ERREUR-PARAMS-VIDE';
        } else {
            $this->AchatGeneralService->saveAchatGeneral($data, $achatGeneral);
            $codeStatut = "OK";
        }

        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/achat-general/delete/{id}', name: 'ajax_delete_achat_general')]
    public function ajaxDeleteAchatGeneral(Request $request, $id): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $achatGeneral = $this->AchatGeneralService->getAchatGeneralById($id);
        $this->AchatGeneralService->deleteAchatGeneral($achatGeneral);

        $codeStatut = "OK";

        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/achat-general/list', name: 'list_achat')]
    public function listGeneral(Request $request): Response
    {
        $list = []; // Assuming this will be filled with your data
    
        $currentDate = new \DateTime();
        $currentDate->modify('first day of this month');
        $date_debut = $currentDate->format('Y-m-d');
        $currentDate->modify('last day of this month');
        $date_fin = $currentDate->format('Y-m-d');
        $filterType = 'date';
        $fournisseur = '';

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

            $fournisseur = $request->request->get('fournisseur');
            
            $list = $this->AchatGeneralService->getDateByFiltrage($filterType , $dateDebut , $dateFin , $annee , $mois , $fournisseur);

            $montant = $this->AchatGeneralService->getMontant($filterType , $dateDebut , $dateFin , $annee , $mois , $fournisseur) ?? 0;
            
        }else{
            $list = $this->AchatGeneralService->getAchatGeneral($date_debut , $date_fin );
            $montant = $this->AchatGeneralService->getMontantAchatGeneral($date_debut , $date_fin) ?? 0;
        }

        $dataList = [];

        for ($i=0; $i < count($list); $i++) { 
            $dataList[$i] = $list[$i];
            $dataList[$i]['categorie_id'] = $this->AchatGeneralService->getOneCategorie($list[$i]['categorie_id'])->getCategorie();
            $dataList[$i]['id_fournisseur_id'] = $this->AchatGeneralService->getOneFournisseurs($list[$i]['id_fournisseur_id'])->getRs();
            $dataList[$i]['designation'] = $this->AchatGeneralService->getOneDesignation($list[$i]['id_designation_id'])->getDesignation();
        }
        $fournisseurs =  $this->AchatGeneralService->getFournisseurs();
        return $this->render('achat/listAchatGeneral.html.twig', [
            'controller_name' => 'caisseController',
            'achat' => $dataList,
            'date_debut' => $date_debut,
            'date_fin' => $date_fin,
            'list_years' => $listYears, 
            'list_months' => $listMonths ,
            'list_all_months' => $listAllMonths ,
            'currentYear' => $currentYear ,
            'filter_type'=>$filterType,
            'montant' =>$montant,
            'fournisseurs'=>$fournisseurs,
            'fournisseur'=>$fournisseur
        ]);
    }

}
