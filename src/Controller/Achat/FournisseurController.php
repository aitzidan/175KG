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

    public function __construct(MessageService $messageService, BaseService $baseService, FournisseurService $fournisseurService)
    {
        $this->messageService = $messageService;
        $this->baseService = $baseService;
        $this->fournisseurService = $fournisseurService;
    }

    #[Route('/fournisseur/list', name: 'list_fournisseur')]
    public function index(Request $request): Response
    {
        $list = $this->fournisseurService->getListFournisseur();
        return $this->render('fournisseur/list.html.twig', [
            'fournisseur' => $list
        ]);
    }

    #[Route('/fournisseur/add', name: 'add_fournisseur')]
    public function addFournisseur(): Response
    {
        return $this->render('fournisseur/addFournisseur.html.twig', [
            'date' => new \DateTime('now')
        ]);
    }

    #[Route('/fournisseur/ajaxAddFournisseur', name: 'ajax_add_fournisseur')]
    public function ajaxAddFournisseur(Request $request): Response
    {
        $respObjects = [];
        $codeStatut = "";

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
        $checkData = $this->fournisseurService->checkData($data);

        if (!$checkData) {
            $codeStatut = 'ERREUR-PARAMS-VIDE';
        } else {
            $this->fournisseurService->addFournisseur($data);
            $codeStatut = "OK";
        }

        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->messageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/fournisseur/update/{id}', name: 'update_fournisseur')]
    public function updateFournisseur($id): Response
    {
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

        $checkData = $this->fournisseurService->checkData($data);
        if (!$checkData) {
            $codeStatut = 'ERREUR-PARAMS-VIDE';
        } else {
            $this->fournisseurService->saveFournisseur($data, $fournisseur);
            $codeStatut = "OK";
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

        $fournisseur = $this->fournisseurService->getFournisseur($id);

        $this->fournisseurService->deleteFournisseur($fournisseur);

        $codeStatut = "OK";

        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->messageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }
}
