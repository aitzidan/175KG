<?php

namespace App\Controller\Analyse;

use App\Service\BaseService;
use App\Service\RevientService;
use App\Service\MessageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RevientController extends AbstractController
{
    private MessageService $MessageService;
    private BaseService $BaseService;
    private RevientService $RevientService;

    public function __construct(MessageService $MessageService, BaseService $BaseService, RevientService $RevientService)
    {
        $this->MessageService = $MessageService;
        $this->BaseService = $BaseService;
        $this->RevientService = $RevientService;
    }

    #[Route('/revient/list', name: 'list_revient')]
    public function index(Request $request): Response
    {
        $chckAccess = $this->BaseService->Role(119);
        if ($chckAccess == 0) {
            return $this->redirectToRoute('login');
        } elseif ($chckAccess == 2) {
            return $this->redirectToRoute('listUsers');
        }

        $list = $this->RevientService->getListRevient();

        return $this->render('revient/list.html.twig', [
            'revient' => $list
        ]);
    }

    #[Route('/revient/add', name: 'add_revient')]
    public function addRevient(): Response
    {
        $chckAccess = $this->BaseService->Role(120);

        if ($chckAccess == 0) {
            return $this->redirectToRoute('login');
        } elseif ($chckAccess == 2) {
            return $this->redirectToRoute('listUsers');
        }

        return $this->render('revient/addRevient.html.twig', [
            'date' => new \DateTime('now')
        ]);
    }

    #[Route('/revient/ajaxAddRevient', name: 'ajax_add_revient')]
    public function ajaxAddRevient(Request $request): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $chckAccess = $this->BaseService->Role(120);
        if ($chckAccess == 0) {
            return $this->json($this->BaseService->errorAccess());
        } elseif ($chckAccess == 2) {
            return $this->json($this->BaseService->errorAccess());
        }

        $data = [
            'nom_produit' => $request->get('nom_produit'),
            'nombre_unite' => $request->get('nombre_unite'),
            'total_ht' => $request->get('total_ht'),
            'prix_revient_ht' => $request->get('prix_revient_ht'),
            'prix_vente_ht' => $request->get('prix_vente_ht'),
            'marge_brute' => $request->get('marge_brute'),
            'taux_marge' => $request->get('taux_marge'),
            'coefficient_marge' => $request->get('coefficient_marge')
        ];
        
        $dataDetails = [];
        $designation = $request->get('designation');
        $fournisseur = $request->get('fournisseur');
        $unite = $request->get('unite');
        $cout_achat = $request->get('cout_achat');
        $unite_necessaire = $request->get('unite_necessaire');
        $prix_ht = $request->get('prix_ht');
        $checkDataDetails = true;
        if(isset($designation)){
            for ($i=0; $i < count($designation); $i++) { 
                $dataDetails[$i]['designation'] = $designation[$i];
                $dataDetails[$i]['fournisseur'] = $fournisseur[$i];
                $dataDetails[$i]['unite'] = $unite[$i];
                $dataDetails[$i]['cout_achat'] = $cout_achat[$i];
                $dataDetails[$i]['unite_necessaire'] = $unite_necessaire[$i];
                $dataDetails[$i]['prix_ht'] = $prix_ht[$i];
                
                if(!$designation[$i] || !$fournisseur[$i] ||!$unite[$i] ||!$cout_achat[$i] ||!$unite_necessaire[$i] ||!$prix_ht[$i]){
                    $checkDataDetails = false;
                }
            }

            $checkData = $this->RevientService->checkData($data);

            if (!$checkData ) {
                $codeStatut = 'ERREUR-PARAMS-VIDE';
            } else {
                $checkData = $this->RevientService->addRevient($data,$dataDetails);
                $codeStatut = "OK";
            }

        }else{
            $codeStatut = 'ERREUR-PARAMS-VIDE';
        }



        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/revient/update/{id}', name: 'update_revient')]
    public function updateRevient($id): Response
    {
        $chckAccess = $this->BaseService->Role(121);
        if ($chckAccess == 0) {
            return $this->redirectToRoute('login');
        } elseif ($chckAccess == 2) {
            return $this->redirectToRoute('listUsers');
        }

        $revient = $this->RevientService->getRevient($id);

        return $this->render('revient/updateRevient.html.twig', [
            'revient' => $revient,
            'id' => $id
        ]);
    }

    #[Route('/revient/ajaxUpdateRevient/{id}', name: 'ajax_update_revient')]
    public function ajaxUpdateRevient(Request $request, $id): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $chckAccess = $this->BaseService->Role(121);
        if ($chckAccess == 0) {
            return $this->json($this->BaseService->errorAccess());
        } elseif ($chckAccess == 2) {
            return $this->json($this->BaseService->errorAccess());
        }

        $revient = $this->RevientService->getRevient($id);
        $data = [
            'nom_produit' => $request->get('nom_produit'),
            'nombre_unite' => $request->get('nombre_unite'),
            'total_ht' => $request->get('total_ht'),
            'prix_revient_ht' => $request->get('prix_revient_ht'),
            'prix_vente_ht' => $request->get('prix_vente_ht'),
            'marge_brute' => $request->get('marge_brute'),
            'taux_marge' => $request->get('taux_marge'),
            'coefficient_marge' => $request->get('coefficient_marge')
        ];
        
        $dataDetails = [];
        $designation = $request->get('designation');
        $fournisseur = $request->get('fournisseur');
        $unite = $request->get('unite');
        $cout_achat = $request->get('cout_achat');
        $unite_necessaire = $request->get('unite_necessaire');
        $prix_ht = $request->get('prix_ht');
        $checkDataDetails = true;
        if(isset($designation)){
            for ($i=0; $i < count($designation); $i++) { 
                $dataDetails[$i]['designation'] = $designation[$i];
                $dataDetails[$i]['fournisseur'] = $fournisseur[$i];
                $dataDetails[$i]['unite'] = $unite[$i];
                $dataDetails[$i]['cout_achat'] = $cout_achat[$i];
                $dataDetails[$i]['unite_necessaire'] = $unite_necessaire[$i];
                $dataDetails[$i]['prix_ht'] = $prix_ht[$i];
                
                if(!$designation[$i] || !$fournisseur[$i] ||!$unite[$i] ||!$cout_achat[$i] ||!$unite_necessaire[$i] ||!$prix_ht[$i]){
                    $checkDataDetails = false;
                }
            }

            $checkData = $this->RevientService->checkData($data);

            if (!$checkData ) {
                $codeStatut = 'ERREUR-PARAMS-VIDE';
            } else {
                $checkData = $this->RevientService->saveRevient($data,$dataDetails,$revient);
                $codeStatut = "OK";
            }

        }else{
            $codeStatut = 'ERREUR-PARAMS-VIDE';
        }

        

        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/revient/delete/{id}', name: 'ajax_delete_revient')]
    public function ajaxDeleteRevient(Request $request, $id): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $chckAccess = $this->BaseService->Role(122);
        if ($chckAccess == 0) {
            return $this->json($this->BaseService->errorAccess());
        } elseif ($chckAccess == 2) {
            return $this->json($this->BaseService->errorAccess());
        }

        $revient = $this->RevientService->getRevient($id);

        $details = $this->RevientService->getDetailsRevient($id);
        foreach ($details as $d) {
            $this->RevientService->deleteDetailRevient($d);
            # code...
        }
        $this->RevientService->deleteRevient($revient);

        $codeStatut = "OK";

        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/revient/getDetailsRevient/{id}', name: 'getDetailsRevient')]
    public function getDetailsBl($id): Response
    {
        $respObjects = [];
        $codeStatut = "";


        $data = $this->RevientService->getDetailsRevient($id);
        $respObjects["data"] = $data;

        $codeStatut = "OK";

        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->MessageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

}
