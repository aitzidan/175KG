<?php

namespace App\Controller\Expedition;

use App\Service\BaseService;
use App\Service\ProduitService;
use App\Service\MessageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProduitController extends AbstractController
{
    private $ProduitService;
    private $messageService;

    public function __construct(ProduitService $ProduitService, MessageService $messageService,
    private BaseService $BaseService
    )
    {
        $this->ProduitService = $ProduitService;
        $this->messageService = $messageService;
    }

    #[Route('/produit/list', name: 'list_produit')]
    public function index(): Response
    {
        $list = $this->ProduitService->getListProduit();
        
        $chckAccess = $this->BaseService->Role(118);
        if($chckAccess == 0){
            return $this->redirectToRoute('login');
        }else if ($chckAccess == 2){
            return $this->redirectToRoute('listUsers');
        }

        return $this->render('expedition/produit/list.html.twig', [
            'produits' => $list
        ]);
    }

    #[Route('/produit/listAjaxProduit', name: 'list_produit_ajax')]
    public function listAjaxProduit(): Response
    {

        $list = $this->ProduitService->getListProduit();
        $codeStatut = 'OK';
        $respObjects["data"] = $list;
        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->messageService->checkMessage($codeStatut);
        return $this->json($respObjects);
        
    }


    #[Route('/produit/add', name: 'add_produit')]
    public function addProduit(): Response
    {
        
        $chckAccess = $this->BaseService->Role(115);
        if($chckAccess == 0){
            return $this->redirectToRoute('login');
        }else if ($chckAccess == 2){
            return $this->redirectToRoute('listUsers');
        }

        return $this->render('expedition/produit/addProduit.html.twig', [
            'date' => new \DateTime('now')
        ]);
    }

    #[Route('/produit/ajaxAddProduit', name: 'ajax_add_produit')]
    public function ajaxAddProduit(Request $request): Response
        {
            $chckAccess = $this->BaseService->Role(115);
            if($chckAccess == 0){
                return $this->json($this->BaseService->errorAccess());
            }else if ($chckAccess == 2){
                return $this->json($this->BaseService->errorAccess());
            }


        $respObjects = [];
        $codeStatut = "";

        $data = [
            'code' => $request->get('code'),
            'produit' => $request->get('produit'),
            'unite' => $request->get('unite'),
        ];
        $checkData = $this->ProduitService->checkData($data);

        if (!$checkData) {
            $codeStatut = 'ERREUR-PARAMS-VIDE';
        } else {
            $this->ProduitService->addProduit($data);
            $codeStatut = "OK";
        }

        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->messageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/produit/update/{id}', name: 'update_produit')]
    public function updateProduit($id): Response
    {
        $chckAccess = $this->BaseService->Role(116);
        if($chckAccess == 0){
            return $this->json($this->BaseService->errorAccess());
        }else if ($chckAccess == 2){
            return $this->json($this->BaseService->errorAccess());
        }

        $produit = $this->ProduitService->getProduit($id);

        return $this->render('expedition/produit/updateProduit.html.twig', [
            'produit' => $produit,
            'id' => $id
        ]);
    }

    #[Route('/produit/ajaxUpdateProduit/{id}', name: 'ajax_update_produit')]
    public function ajaxUpdateProduit(Request $request, $id): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $chckAccess = $this->BaseService->Role(116);
        if($chckAccess == 0){
            return $this->json($this->BaseService->errorAccess());
        }else if ($chckAccess == 2){
            return $this->json($this->BaseService->errorAccess());
        }

        $produit = $this->ProduitService->getProduit($id);
        $data = [
            'code' => $request->get('code'),
            'produit' => $request->get('produit'),
            'unite' => $request->get('unite'),
            'entity' => $request->get('entity'), // Adjust according to your implementation
        ];

        $checkData = $this->ProduitService->checkData($data);
        if (!$checkData) {
            $codeStatut = 'ERREUR-PARAMS-VIDE';
        } else {
            $this->ProduitService->saveProduit($data, $produit);
            $codeStatut = "OK";
        }

        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->messageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/produit/delete/{id}', name: 'ajax_delete_produit')]
    public function ajaxDeleteProduit($id): Response
    {
        $respObjects = [];
        $codeStatut = "";
        
        $chckAccess = $this->BaseService->Role(117);
        if($chckAccess == 0){
            return $this->json($this->BaseService->errorAccess());
        }else if ($chckAccess == 2){
            return $this->json($this->BaseService->errorAccess());
        }

        $produit = $this->ProduitService->getProduit($id);

        if ($produit) {
            $this->ProduitService->deleteProduit($produit);
            $codeStatut = "OK";
        } else {
            $codeStatut = "ERREUR-PRODUIT-INEXISTANT";
        }

        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->messageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }
}
