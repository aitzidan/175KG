<?php

namespace App\Controller\Reglement;

use App\Service\BaseService;
use App\Service\EntityService;
use App\Service\ReglementService;
use App\Service\MessageService;
use App\Service\FournisseurService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReglementController extends AbstractController
{
    private MessageService $MessageService;
    private ReglementService $ReglementService;
    private FournisseurService $FournisseurService;
    private EntityService $EntityService;
    private BaseService $BaseService;

    public function __construct(MessageService $MessageService, ReglementService $ReglementService, FournisseurService $FournisseurService, EntityService $EntityService, BaseService $BaseService)
    {
        $this->MessageService = $MessageService;
        $this->ReglementService = $ReglementService;
        $this->FournisseurService = $FournisseurService;
        $this->EntityService = $EntityService;
        $this->BaseService = $BaseService;
    }

    #[Route('/reglement/list', name: 'list_reglement')]
    public function index(Request $request): Response
    {
        
        $chckAccess = $this->BaseService->Role(89);
        if($chckAccess == 0){
            return $this->redirectToRoute('login');
        }else if ($chckAccess == 2){
            return $this->redirectToRoute('listUsers');
        }

        $list = $this->ReglementService->getListReglement();

        return $this->render('reglement/reglement/list.html.twig', [
            'reglement' => $list,
        ]);
    }

    #[Route('/reglement/add', name: 'add_reglement')]
    public function addReglement(): Response
    {
        $chckAccess = $this->BaseService->Role(85);
        if($chckAccess == 0){
            return $this->redirectToRoute('login');
        }else if ($chckAccess == 2){
            return $this->redirectToRoute('listUsers');
        }

        $listFournisseur = $this->FournisseurService->getListFournisseur();
        $listEntity = $this->EntityService->getListEntity();
        

        return $this->render('/reglement/reglement/addReglement.html.twig', [
           'listFournisseur' => $listFournisseur,
           'listEntity' => $listEntity
        ]);
    }

    #[Route('/reglement/ajaxAddReglement', name: 'ajax_add_reglement')]
    public function ajaxAddReglement(Request $request): Response
    {
        $respObjects = [];
        $codeStatut = "";
        

        $chckAccess = $this->BaseService->Role(85);
        if($chckAccess == 0){
            return $this->json($this->BaseService->errorAccess());
        }else if ($chckAccess == 2){
            return $this->json($this->BaseService->errorAccess());
        }


        $data = [
            'numero' => $request->get('numero'),
            'date' => new \DateTime($request->get('date')),
            'destinataire' => $request->get('destinataire'),
            'numeroFacture' => $request->get('numeroFacture'),
            'details' => $request->get('details'),
            'montant' => $request->get('montant'),
            'date_encaissement' => new \DateTime($request->get('date_encaissement')),
            'entity' => $request->get('entity'),
            'banque' => $request->get('banque'),
        ];
        $respObjects["data"] = $data;

        $checkData = $this->ReglementService->checkData($data);

        if (!$checkData) {
            $codeStatut = 'ERREUR-PARAMS-VIDE';
        } else {
            $checkData = $this->ReglementService->addReglement($data);
            $codeStatut = "OK";
        }
       
        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/reglement/update/{id}', name: 'update_reglement')]
    public function updateReglement($id): Response
    {

        $chckAccess = $this->BaseService->Role(86);
        if($chckAccess == 0){
            return $this->redirectToRoute('login');
        }else if ($chckAccess == 2){
            return $this->redirectToRoute('listUsers');
        }

        $reglement = $this->ReglementService->getReglement($id);
        $listFournisseur = $this->FournisseurService->getListFournisseur();
        $listEntity = $this->EntityService->getListEntity();

        return $this->render('/reglement/reglement/updateReglement.html.twig', [
           'reglement' => $reglement,
           'id' => $id,
           'listFournisseur' => $listFournisseur,
           'listEntity' => $listEntity
        ]);
    }
    
    #[Route('/reglement/ajaxUpdateReglement/{id}', name: 'ajax_update_reglement')]
    public function ajaxUpdateReglement(Request $request, $id): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $chckAccess = $this->BaseService->Role(86);
        if($chckAccess == 0){
            return $this->json($this->BaseService->errorAccess());
        }else if ($chckAccess == 2){
            return $this->json($this->BaseService->errorAccess());
        }


        $reglement = $this->ReglementService->getReglement($id);
        $data = [
            'numero' => $request->get('numero'),
            'date' => new \DateTime($request->get('date')),
            'destinataire' => $request->get('destinataire'),
            'numeroFacture' => $request->get('numeroFacture'),
            'details' => $request->get('details'),
            'montant' => $request->get('montant'),
            'date_encaissement' => new \DateTime($request->get('date_encaissement')),
            'entity' => $request->get('entity'),
            'banque' => $request->get('banque'),
        ];

        $checkData = $this->ReglementService->checkData($data);
        if (!$checkData) {
            $codeStatut = 'ERREUR-PARAMS-VIDE';
        } else {
            $checkData = $this->ReglementService->saveReglement($data, $reglement);
            $codeStatut = "OK";
        }
    
        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/reglement/delete/{id}', name: 'ajax_delete_reglement')]
    public function ajaxDeleteReglement(Request $request, $id): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $chckAccess = $this->BaseService->Role(87);
        if($chckAccess == 0){
            return $this->json($this->BaseService->errorAccess());
        }else if ($chckAccess == 2){
            return $this->json($this->BaseService->errorAccess());
        }


        $reglement = $this->ReglementService->getReglement($id);
        
        $this->ReglementService->deleteReglement($reglement);

        $codeStatut = "OK";   
    
        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/reglement/validate/{id}', name: 'ajax_validate_reglement')]
    public function ajaxValidateReglement(Request $request, $id): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $chckAccess = $this->BaseService->Role(88);
        if($chckAccess == 0){
            return $this->json($this->BaseService->errorAccess());
        }else if ($chckAccess == 2){
            return $this->json($this->BaseService->errorAccess());
        }


        $reglement = $this->ReglementService->getReglement($id);
        
        $this->ReglementService->validateReglement($reglement);

        $codeStatut = "OK";   
    
        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/reglement/getReglementByFournisseur/{id}', name: 'ajax_getReglementByFournisseur')]
    public function getReglementByFournisseur(Request $request, $id): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $reglement = $this->ReglementService->getReglementByFournisseur($id);
        
        $respObjects["data"] = $reglement;

        $codeStatut = "OK";   
    
        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }
}
