<?php

namespace App\Controller\Reglement;

use App\Service\BaseService;
use App\Service\ChequeService;
use App\Service\MessageService;
use App\Service\FournisseurService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ChequeController extends AbstractController
{
    private MessageService $MessageService;
    private ChequeService $ChequeService;
    private FournisseurService $FournisseurService;
    private BaseService $BaseService;

    public function __construct(MessageService $MessageService, ChequeService $ChequeService, FournisseurService $FournisseurService, BaseService $BaseService)
    {
        $this->MessageService = $MessageService;
        $this->ChequeService = $ChequeService;
        $this->FournisseurService = $FournisseurService;
        $this->BaseService = $BaseService;
    }

    #[Route('/cheque/list', name: 'list_cheque')]
    public function index(Request $request): Response
    {
        
        $chckAccess = $this->BaseService->Role(94);
        if($chckAccess == 0){
            return $this->redirectToRoute('login');
        }else if ($chckAccess == 2){
            return $this->redirectToRoute('listUsers');
        }
    
        $list = $this->ChequeService->getListCheque();

        return $this->render('reglement/cheque/list.html.twig', [
            'cheque' => $list,
        ]);
    }

    #[Route('/cheque/add', name: 'add_cheque')]
    public function addCheque(): Response
    {
                
        $chckAccess = $this->BaseService->Role(90);
        if($chckAccess == 0){
            return $this->redirectToRoute('login');
        }else if ($chckAccess == 2){
            return $this->redirectToRoute('listUsers');
        }
    

        $listFournisseur = $this->FournisseurService->getListFournisseur();

        return $this->render('/reglement/cheque/addCheque.html.twig', [
           'listFournisseur' => $listFournisseur
        ]);
    }

    #[Route('/cheque/ajaxAddCheque', name: 'ajax_add_cheque')]
    public function ajaxAddCheque(Request $request): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $chckAccess = $this->BaseService->Role(90);
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
        ];
        $respObjects["data"] = $data;

        $checkData = $this->ChequeService->checkData($data);

        if (!$checkData) {
            $codeStatut = 'ERREUR-PARAMS-VIDE';
        } else {
            $checkData = $this->ChequeService->addCheque($data);
            $codeStatut = "OK";
        }
       
        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/cheque/update/{id}', name: 'update_cheque')]
    public function updateCheque($id): Response
    {
                
        $chckAccess = $this->BaseService->Role(91);
        if($chckAccess == 0){
            return $this->redirectToRoute('login');
        }else if ($chckAccess == 2){
            return $this->redirectToRoute('listUsers');
        }
    
        $cheque = $this->ChequeService->getCheque($id);
        $listFournisseur = $this->FournisseurService->getListFournisseur();

        return $this->render('/reglement/cheque/updateCheque.html.twig', [
           'cheque' => $cheque,
           'id' => $id,
           'listFournisseur' => $listFournisseur
        ]);
    }
    
    #[Route('/cheque/ajaxUpdateCheque/{id}', name: 'ajax_update_cheque')]
    public function ajaxUpdateCheque(Request $request, $id): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $chckAccess = $this->BaseService->Role(91);
        if($chckAccess == 0){
            return $this->json($this->BaseService->errorAccess());
        }else if ($chckAccess == 2){
            return $this->json($this->BaseService->errorAccess());
        }

        $cheque = $this->ChequeService->getCheque($id);
        $data = [
            'numero' => $request->get('numero'),
            'date' => new \DateTime($request->get('date')),
            'destinataire' => $request->get('destinataire'),
            'numeroFacture' => $request->get('numeroFacture'),
            'details' => $request->get('details'),
            'montant' => $request->get('montant'),
            'date_encaissement' => new \DateTime($request->get('date_encaissement')),
        ];

        $checkData = $this->ChequeService->checkData($data);
        if (!$checkData) {
            $codeStatut = 'ERREUR-PARAMS-VIDE';
        } else {
            $checkData = $this->ChequeService->saveCheque($data, $cheque);
            $codeStatut = "OK";
        }
    
        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/cheque/delete/{id}', name: 'ajax_delete_cheque')]
    public function ajaxDeleteCheque(Request $request, $id): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $chckAccess = $this->BaseService->Role(92);
        if($chckAccess == 0){
            return $this->json($this->BaseService->errorAccess());
        }else if ($chckAccess == 2){
            return $this->json($this->BaseService->errorAccess());
        }

        $cheque = $this->ChequeService->getCheque($id);
        
        $this->ChequeService->deleteCheque($cheque);

        $codeStatut = "OK";   
    
        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/cheque/validate/{id}', name: 'ajax_validate_cheque')]
    public function ajaxValidateCheque(Request $request, $id): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $chckAccess = $this->BaseService->Role(93);
        if($chckAccess == 0){
            return $this->json($this->BaseService->errorAccess());
        }else if ($chckAccess == 2){
            return $this->json($this->BaseService->errorAccess());
        }

        $cheque = $this->ChequeService->getCheque($id);
        
        $this->ChequeService->validateCheque($cheque);

        $codeStatut = "OK";   
    
        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/cheque/getChequeByFournisseur/{id}', name: 'ajax_getChequeByFournisseur')]
    public function getChequeByFournisseur(Request $request, $id): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $cheque = $this->ChequeService->getChequeByFournisseur($id);
        
        $respObjects["data"] = $cheque;

        $codeStatut = "OK";   
    
        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/cheque/validate', name: 'ajax_validateCheque')]
    public function validateCheque(Request $request): Response
    {
        $respObjects = [];
        $codeStatut = "ERROR"; // Default to error status
        
        $chckAccess = $this->BaseService->Role(93);
        if($chckAccess == 0){
            return $this->json($this->BaseService->errorAccess());
        }else if ($chckAccess == 2){
            return $this->json($this->BaseService->errorAccess());
        }

        // Decode the JSON payload from the request
        $data_list = json_decode($request->getContent(), true);
        $ids = $data_list['selectedIds'] ?? []; // Use null coalescing operator to handle missing 'selectedIds'
        $respObjects["data"] = $ids;

        // Fetch the list of cheques
        $list = $this->ChequeService->getListCheque();
        
        $alreadyValidate = [];
        foreach ($list as $cheque) {
            if ($cheque->getEtat() == 2) {
                $alreadyValidate[] = $cheque->getId();
            }
        }
        $respObjects["alreadyValidate"] = $alreadyValidate;

        // Update cheques that are already validated but not in the selectedIds
        foreach ($alreadyValidate as $validateId) {
            if (!in_array($validateId, $ids)) {
                $cheque = $this->ChequeService->getCheque($validateId);
                $cheque->setEtat(1);
            }
        }

        // Update the cheques from the selectedIds to be in state 2
        foreach ($ids as $id) {
            $cheque = $this->ChequeService->getCheque($id);
            $cheque->setEtat(2);
        }

        // Save all changes
        $this->ChequeService->save();

        $codeStatut = "OK"; // Set success status

        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);

        return $this->json($respObjects);
    }

}
