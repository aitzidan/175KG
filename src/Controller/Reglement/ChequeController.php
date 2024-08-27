<?php

namespace App\Controller\Reglement;

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

    public function __construct(MessageService $MessageService, ChequeService $ChequeService, FournisseurService $FournisseurService)
    {
        $this->MessageService = $MessageService;
        $this->ChequeService = $ChequeService;
        $this->FournisseurService = $FournisseurService;
    }

    #[Route('/cheque/list', name: 'list_cheque')]
    public function index(Request $request): Response
    {
        $list = $this->ChequeService->getListCheque();

        return $this->render('reglement/cheque/list.html.twig', [
            'cheque' => $list,
        ]);
    }

    #[Route('/cheque/add', name: 'add_cheque')]
    public function addCheque(): Response
    {
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

        $cheque = $this->ChequeService->getCheque($id);
        
        $this->ChequeService->deleteCheque($cheque);

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
}
