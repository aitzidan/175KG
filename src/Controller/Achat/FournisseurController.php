<?php

namespace App\Controller\Achat;

use App\Service\BaseService;
use App\Service\FournisseurService;
use App\Service\MessageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FournisseurController extends AbstractController
{
    private MessageService $messageService;
    private BaseService $baseService;
    private FournisseurService $fournisseurService;

    public function __construct(
        private BaseService $BaseService ,
    MessageService $messageService, BaseService $baseService, FournisseurService $fournisseurService)
    {
        $this->messageService = $messageService;
        $this->baseService = $baseService;
        $this->fournisseurService = $fournisseurService;
    }

    #[Route('/fournisseur/list', name: 'list_fournisseur')]
    public function index(Request $request): Response
    {
        
        $chckAccess = $this->BaseService->Role(106);
        if($chckAccess == 0){
            return $this->redirectToRoute('login');
        }else if ($chckAccess == 2){
            return $this->redirectToRoute('listUsers');
        }

        $list = $this->fournisseurService->getListFournisseur();
        return $this->render('fournisseur/list.html.twig', [
            'fournisseur' => $list
        ]);
    }

    #[Route('/fournisseur/add', name: 'add_fournisseur')]
    public function addFournisseur(): Response
    {
        
        $chckAccess = $this->BaseService->Role(103);
        if($chckAccess == 0){
            return $this->redirectToRoute('login');
        }else if ($chckAccess == 2){
            return $this->redirectToRoute('listUsers');
        }

        return $this->render('fournisseur/addFournisseur.html.twig', [
            'date' => new \DateTime('now')
        ]);
    }

    #[Route('/fournisseur/ajaxAddFournisseur', name: 'ajax_add_fournisseur')]
    public function ajaxAddFournisseur(Request $request): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $chckAccess = $this->BaseService->Role(103);
        if($chckAccess == 0){
            return $this->json($this->BaseService->errorAccess());
        }else if ($chckAccess == 2){
            return $this->json($this->BaseService->errorAccess());
        }

        $data = [
            'rs' => $request->get('rs'),
            'telephone' => $request->get('telephone'),
            'email' => $request->get('email'),
            'adresse' => $request->get('adresse'),
            'nom' => $request->get('nom'),
            'prenom' => $request->get('prenom'),
            'ice' => $request->get('ice'),
            'rc' => $request->get('rc')
        ];

        if (!preg_match('/^\+?[0-9\s\-]+$/', $data['telephone'])) {
            $codeStatut = 'ERREUR-TELEPHONE-INVALID';
        } else {

            $checkData = $this->fournisseurService->checkData($data);
    
            if (!$checkData) {
                $codeStatut = 'ERREUR-PARAMS-VIDE';
            } else {
                $this->fournisseurService->addFournisseur($data);
                $codeStatut = "OK";
            }
        }

        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->messageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/fournisseur/update/{id}', name: 'update_fournisseur')]
    public function updateFournisseur($id): Response
    {
        
        $chckAccess = $this->BaseService->Role(104);
        if($chckAccess == 0){
            return $this->redirectToRoute('login');
        }else if ($chckAccess == 2){
            return $this->redirectToRoute('listUsers');
        }

        $fournisseur = $this->fournisseurService->getFournisseur($id);

        return $this->render('fournisseur/updateFournisseur.html.twig', [
            'fournisseur' => $fournisseur,
            'id' => $id
        ]);
    }

    #[Route('/fournisseur/ajaxUpdateFournisseur/{id}', name: 'ajax_update_fournisseur')]
    public function ajaxUpdateFournisseur(Request $request, $id): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $chckAccess = $this->BaseService->Role(104);
        if($chckAccess == 0){
            return $this->json($this->BaseService->errorAccess());
        }else if ($chckAccess == 2){
            return $this->json($this->BaseService->errorAccess());
        }

        $fournisseur = $this->fournisseurService->getFournisseur($id);
        $data = [
            'rs' => $request->get('rs'),
            'telephone' => $request->get('telephone'),
            'email' => $request->get('email'),
            'adresse' => $request->get('adresse'),
            'nom' => $request->get('nom'),
            'prenom' => $request->get('prenom'),
            'ice' => $request->get('ice'),
            'rc' => $request->get('rc')
        ];

          // Validate telephone number
        if (!preg_match('/^\+?[0-9\s\-]+$/', $data['telephone'])) {
            $codeStatut = 'ERREUR-TELEPHONE-INVALID';
        } else {
            // Validate other data
            $checkData = $this->fournisseurService->checkData($data);
            if (!$checkData) {
                $codeStatut = 'ERREUR-PARAMS-VIDE';
            } else {
                // Save fournisseur
                $this->fournisseurService->saveFournisseur($data, $fournisseur);
                $codeStatut = "OK";
            }
        }


        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->messageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/fournisseur/delete/{id}', name: 'ajax_delete_fournisseur')]
    public function ajaxDeleteFournisseur($id): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $chckAccess = $this->BaseService->Role(105);
        if($chckAccess == 0){
            return $this->json($this->BaseService->errorAccess());
        }else if ($chckAccess == 2){
            return $this->json($this->BaseService->errorAccess());
        }

        $fournisseur = $this->fournisseurService->getFournisseur($id);

        $this->fournisseurService->deleteFournisseur($fournisseur);

        $codeStatut = "OK";

        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->messageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/fournisseur/listAjaxFournisseur', name: 'listAjaxFournisseur')]
    public function listAjaxFournisseur(): Response
    {
        
        
        $codeStatut = "OK";
        
        $data = $this->fournisseurService->getListFournisseur();
        $respObjects['data'] = $data;
        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->messageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }
}
