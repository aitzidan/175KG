<?php

namespace App\Controller\Achat;

use App\Service\BaseService;
use App\Service\EntityService;
use App\Service\MessageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EntityController extends AbstractController
{
    private MessageService $MessageService;
    private BaseService $BaseService;
    private EntityService $EntityService;

    public function __construct(MessageService $MessageService, BaseService $BaseService, EntityService $EntityService)
    {
        $this->MessageService = $MessageService;
        $this->BaseService = $BaseService;
        $this->EntityService = $EntityService;
    }

    #[Route('/entity/list', name: 'list_entity')]
    public function index(Request $request): Response
    {
        $chckAccess = $this->BaseService->Role(114);
        if($chckAccess == 0){
            return $this->redirectToRoute('login');
        }else if ($chckAccess == 2){
            return $this->redirectToRoute('listUsers');
        }


        $list = $this->EntityService->getListEntity();
    
        return $this->render('entity/list.html.twig', [
            'entity' => $list
        ]);
    }

    #[Route('/entity/add', name: 'add_entity')]
    public function addEntity(): Response
    {
        $chckAccess = $this->BaseService->Role(111);
        if($chckAccess == 0){
            return $this->redirectToRoute('login');
        }else if ($chckAccess == 2){
            return $this->redirectToRoute('listUsers');
        }


        return $this->render('entity/addEntity.html.twig', [
           'date' => new \DateTime('now')
        ]);
    }

    #[Route('/entity/ajaxAddEntity', name: 'ajax_add_entity')]
    public function ajaxAddEntity(Request $request): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $chckAccess = $this->BaseService->Role(111);
        if($chckAccess == 0){
            return $this->json($this->BaseService->errorAccess());
        }else if ($chckAccess == 2){
            return $this->json($this->BaseService->errorAccess());
        }

        $data = [
            'entity' => $request->get('entity'),
        ];
        $checkData = $this->EntityService->checkData($data);

        if (!$checkData) {
            $codeStatut = 'ERREUR-PARAMS-VIDE';
        } else {
            $checkData = $this->EntityService->addEntity($data);
            $codeStatut = "OK";
        }
        
        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/entity/update/{id}', name: 'update_entity')]
    public function updateEntity($id): Response
    {

        $chckAccess = $this->BaseService->Role(112);
        if($chckAccess == 0){
            return $this->redirectToRoute('login');
        }else if ($chckAccess == 2){
            return $this->redirectToRoute('listUsers');
        }


        $entity = $this->EntityService->getEntity($id);

        return $this->render('/entity/updateEntity.html.twig', [
           'entity' => $entity,
           'id' => $id
        ]);
    }
    
    #[Route('/entity/ajaxUpdateEntity/{id}', name: 'ajax_update_entity')]
    public function ajaxUpdateEntity(Request $request, $id): Response
    {
        $chckAccess = $this->BaseService->Role(112);
        if($chckAccess == 0){
            return $this->json($this->BaseService->errorAccess());
        }else if ($chckAccess == 2){
            return $this->json($this->BaseService->errorAccess());
        }

        $respObjects = [];
        $codeStatut = "";

       

        $entity = $this->EntityService->getEntity($id);
        $data = [
            'entity' => $request->get('entity')
        ];

        $checkData = $this->EntityService->checkData($data);
        if (!$checkData) {
            $codeStatut = 'ERREUR-PARAMS-VIDE';
        } else {
            $checkData = $this->EntityService->saveEntity($data, $entity);
            $codeStatut = "OK";
        }
    
        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/entity/delete/{id}', name: 'ajax_delete_entity')]
    public function ajaxDeleteEntity(Request $request, $id): Response
    {
        $chckAccess = $this->BaseService->Role(113);
        if($chckAccess == 0){
            return $this->json($this->BaseService->errorAccess());
        }else if ($chckAccess == 2){
            return $this->json($this->BaseService->errorAccess());
        }

        $respObjects = [];
        $codeStatut = "";

        $entity = $this->EntityService->getEntity($id);
        
        $this->EntityService->deleteEntity($entity);

        $codeStatut = "OK";   
    
        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }
}
