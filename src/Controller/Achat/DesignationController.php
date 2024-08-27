<?php

namespace App\Controller\Achat;

use App\Service\BaseService;
use App\Service\CategorieService;
use App\Service\DesignationService;
use App\Service\MessageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DesignationController extends AbstractController
{
    private MessageService $MessageService;
    private BaseService $BaseService;
    private DesignationService $DesignationService;
    private CategorieService $CategorieService;

    public function __construct(MessageService $MessageService,BaseService $BaseService , DesignationService $DesignationService, CategorieService $CategorieService)
    {
        $this->MessageService = $MessageService;
        $this->BaseService = $BaseService;
        $this->DesignationService = $DesignationService;
        $this->CategorieService = $CategorieService;
    }

    #[Route('/designation/list', name: 'list_designation')]
    public function index(Request $request): Response
    {
        $list = []; // Assuming this will be filled with your data
    
        $list = $this->DesignationService->getListDesignation();

    
        return $this->render('designation/list.html.twig', [
            'designation' => $list,
        ]);
    }

    #[Route('/designation/add', name: 'add_designation')]
    public function addDesignation(): Response
    {
        $listCategorie =  $this->CategorieService->getListCategorie();

        return $this->render('designation/addDesignation.html.twig', [
           'listCategorie'=>$listCategorie
        ]);
    }

    #[Route('/designation/ajaxAddDesignation', name: 'ajax_add_designation')]
    public function ajaxAddDesignation(Request $request): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $data = [
            'designation' => $request->get('designation'),
            'categorie' => $request->get('categorie')
        ];
        $checkData = $this->DesignationService->checkData($data);

        if (!$checkData) {
            $codeStatut = 'ERREUR-PARAMS-VIDE';
        } else {
            $checkData = $this->DesignationService->addDesignation($data);
            $codeStatut = "OK";
        }
       
        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/designation/update/{id}', name: 'update_designation')]
    public function updateDesignation($id): Response
    {
        $designation = $this->DesignationService->getDesignation($id);
        $listCategorie =  $this->CategorieService->getListCategorie();

        return $this->render('/designation/updateDesignation.html.twig', [
           'designation'=>$designation,
           'id'=>$id,
           'listCategorie'=>$listCategorie
        ]);
    }
    
    #[Route('/designation/ajaxUpdateDesignation/{id}', name: 'ajax_update_designation')]
    public function ajaxUpdateDesignation(Request $request, $id): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $designation = $this->DesignationService->getDesignation($id);
        $data = [
            'designation' => $request->get('designation'),
            'categorie' => $request->get('categorie')
        ];

        $checkData = $this->DesignationService->checkData($data);
        if (!$checkData) {
            $codeStatut = 'ERREUR-PARAMS-VIDE';
        } else {
            $checkData = $this->DesignationService->saveDesignation($data, $designation);
            $codeStatut = "OK";
        }
    
        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/designation/delete/{id}', name: 'ajax_delete_designation')]
    public function ajaxDeleteDesignation(Request $request, $id): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $designation = $this->DesignationService->getDesignation($id);
        
        $this->DesignationService->deleteDesignation($designation);

        $codeStatut = "OK";   
    
        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/designation/getDesignationByCat/{id}', name: 'ajax_getDesignationByCat')]
    public function getDesignationByCat(Request $request, $id): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $designation = $this->DesignationService->getDesignationByCat($id);
        
        $respObjects["data"] = $designation;

        $codeStatut = "OK";   
    
        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

}
