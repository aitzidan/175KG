<?php

namespace App\Controller\Caisse;

use App\Service\BaseService;
use App\Service\CaisseParamService;
use App\Service\MessageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParamCaisseController extends AbstractController
{
    private MessageService $MessageService;
    private BaseService $BaseService;
    private CaisseParamService $CaisseParamService;

    public function __construct(MessageService $MessageService, BaseService $BaseService, CaisseParamService $CaisseParamService)
    {
        $this->MessageService = $MessageService;
        $this->BaseService = $BaseService;
        $this->CaisseParamService = $CaisseParamService;
    }

    #[Route('/caisse-param/list', name: 'list_caisse_param')]
    public function index(Request $request): Response
    {
        $chckAccess = $this->BaseService->Role(110);
        if($chckAccess == 0){
            return $this->redirectToRoute('login');
        }else if ($chckAccess == 2){
            return $this->redirectToRoute('listUsers');
        }

        $list = $this->CaisseParamService->getListCaisse();

        return $this->render('caisse/param_caisse/list.html.twig', [
            'caisse' => $list
        ]);
    }

    #[Route('/caisse-param/add', name: 'add_caisse_param')]
    public function addCaisse(): Response
    {
        $chckAccess = $this->BaseService->Role(107);
        if($chckAccess == 0){
            return $this->redirectToRoute('login');
        }else if ($chckAccess == 2){
            return $this->redirectToRoute('listUsers');
        }

        return $this->render('caisse/param_caisse/addCaisse.html.twig', [
            'date' => new \DateTime('now')
        ]);
    }

    #[Route('/caisse-param/ajaxAddCaisse', name: 'ajax_add_caisse_param')]
    public function ajaxAddCaisse(Request $request): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $chckAccess = $this->BaseService->Role(107);
        if($chckAccess == 0){
            return $this->json($this->BaseService->errorAccess());
        }else if ($chckAccess == 2){
            return $this->json($this->BaseService->errorAccess());
        }

        $data = [
            'caisse' => $request->get('caisse'),
        ];
        $checkData = $this->CaisseParamService->checkData($data);

        if (!$checkData) {
            $codeStatut = 'ERREUR-PARAMS-VIDE';
        } else {
            $this->CaisseParamService->addCaisse($data);
            $codeStatut = "OK";
        }

        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/caisse-param/update/{id}', name: 'update_caisse_param')]
    public function updateCaisse($id): Response
    {
        $chckAccess = $this->BaseService->Role(108);
        if($chckAccess == 0){
            return $this->redirectToRoute('login');
        }else if ($chckAccess == 2){
            return $this->redirectToRoute('listUsers');
        }

        $caisse = $this->CaisseParamService->getCaisse($id);

        return $this->render('caisse/param_caisse/updateCaisse.html.twig', [
           'caisse' => $caisse,
           'id' => $id
        ]);
    }
    
    #[Route('/caisse-param/ajaxUpdateCaisse/{id}', name: 'ajax_update_caisse_param')]
    public function ajaxUpdateCaisse(Request $request, $id): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $chckAccess = $this->BaseService->Role(108);
        if($chckAccess == 0){
            return $this->json($this->BaseService->errorAccess());
        }else if ($chckAccess == 2){
            return $this->json($this->BaseService->errorAccess());
        }

        $caisse = $this->CaisseParamService->getCaisse($id);
        $data = [
            'caisse' => $request->get('caisse')
        ];

        $checkData = $this->CaisseParamService->checkData($data);
        if (!$checkData) {
            $codeStatut = 'ERREUR-PARAMS-VIDE';
        } else {
            $this->CaisseParamService->saveCaisse($data, $caisse);
            $codeStatut = "OK";
        }
    
        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/caisse-param/delete/{id}', name: 'ajax_delete_caisse_param')]
    public function ajaxDeleteCaisse($id): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $chckAccess = $this->BaseService->Role(109);
        if($chckAccess == 0){
            return $this->json($this->BaseService->errorAccess());
        }else if ($chckAccess == 2){
            return $this->json($this->BaseService->errorAccess());
        }

        $caisse = $this->CaisseParamService->getCaisse($id);
        
        $this->CaisseParamService->deleteCaisse($caisse);

        $codeStatut = "OK";   
    
        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }
}
