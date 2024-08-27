<?php

namespace App\Controller\Achat;

use App\Service\BaseService;
use App\Service\CategorieService;
use App\Service\MessageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CategorieController extends AbstractController
{
    private MessageService $MessageService;
    private BaseService $BaseService;
    private CategorieService $CategorieService;

    public function __construct(MessageService $MessageService,BaseService $BaseService , CategorieService $CategorieService)
    {
        $this->MessageService = $MessageService;
        $this->BaseService = $BaseService;
        $this->CategorieService = $CategorieService;
    }

    #[Route('/categorie/list', name: 'list_categorie')]
    public function index(Request $request): Response
    {
        $list = []; // Assuming this will be filled with your data
    
        $list = $this->CategorieService->getListCategorie();
       
    
        return $this->render('categorie/list.html.twig', [
            'categorie' => $list
        ]);
    }

    #[Route('/categorie/add', name: 'add_categorie')]
    public function addCategorie(): Response
    {
        return $this->render('categorie/addCategorie.html.twig', [
           'date'=>new \DateTime('now')
        ]);
    }

    #[Route('/categorie/ajaxAddCategorie', name: 'ajax_add_categorie')]
    public function ajaxAddCategorie(Request $request): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $data = [
            'categorie' => $request->get('categorie'),
        ];
        $checkData = $this->CategorieService->checkData($data);

        if (!$checkData) {
            $codeStatut = 'ERREUR-PARAMS-VIDE';
        } else {
            $checkData = $this->CategorieService->addCategorie($data);
            $codeStatut = "OK";
        }
        
        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/categorie/update/{id}', name: 'update_categorie')]
    public function updateCategorie($id): Response
    {
        $categorie = $this->CategorieService->getCategorie($id);

        return $this->render('/categorie/updateCategorie.html.twig', [
           'categorie'=>$categorie,
           'id'=>$id
        ]);
    }
    
    #[Route('/categorie/ajaxUpdateCategorie/{id}', name: 'ajax_update_categorie')]
    public function ajaxUpdateCategorie(Request $request, $id): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $categorie = $this->CategorieService->getCategorie($id);
        $data = [
            'categorie' => $request->get('categorie')
        ];

        $checkData = $this->CategorieService->checkData($data);
        if (!$checkData) {
            $codeStatut = 'ERREUR-PARAMS-VIDE';
        } else {
            $checkData = $this->CategorieService->saveCategorie($data, $categorie);
            $codeStatut = "OK";
        }
    
        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/categorie/delete/{id}', name: 'ajax_delete_categorie')]
    public function ajaxDeleteCategorie(Request $request, $id): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $categorie = $this->CategorieService->getCategorie($id);
        
        $this->CategorieService->deleteCategorie($categorie);

        $codeStatut = "OK";   
    
        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }



}
