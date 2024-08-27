<?php

namespace App\Controller\Expedition;

use App\Service\BlService;
use App\Service\EntityService;
use App\Service\MessageService;
use Spipu\Html2Pdf\Html2Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlController extends AbstractController
{
    private $blService;
    private $messageService;
    private $EntityService;

    public function __construct(BlService $blService, MessageService $messageService, EntityService $EntityService)
    {
        $this->blService = $blService;
        $this->messageService = $messageService;
        $this->EntityService = $EntityService;
    }

    #[Route('/bl/list', name: 'list_bl')]
    public function index(): Response
    {
        $list = $this->blService->getListBl();
        return $this->render('expedition/bl/list.html.twig', [
            'bls' => $list
        ]);
    }

    #[Route('/bl/add', name: 'add_bl')]
    public function addBl(): Response
    {
        $list = $this->EntityService->getListEntity();
        $data = new \DateTime('now');
        $numero = 'BL-'.$this->blService->findMaxBl().'/'.$data->format('Y');

        return $this->render('expedition/bl/addBl.html.twig', [
            'date' => new \DateTime('now'),
            'listEntity'=>$list,
            'numero'=>$numero
        ]);
    }

    #[Route('/bl/ajaxAddBl', name: 'ajax_add_bl')]
    public function ajaxAddBl(Request $request): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $data = [
            'date' => $request->get('date'),
            'code' => $request->get('code'),
            'entity' => $request->get('entity'),
        ];
        $checkData = $this->blService->checkData($data);

        
        $listIdProduit = $request->get('idProduit');
        $listDesignation = $request->get('designation');
        $listQte = $request->get('qte');
        $listUnite = $request->get('unite');
        $dataDetails = [];

        $checkDataDetails = true;
        if(isset($listIdProduit)){

            for ($i=0; $i < count($listIdProduit); $i++) { 
                $dataDetails[$i]['idProduit'] = $listIdProduit[$i];
                $dataDetails[$i]['designation'] = $listDesignation[$i];
                $dataDetails[$i]['qte'] = $listQte[$i];
                $dataDetails[$i]['unite'] = $listUnite[$i];
                if(!$listIdProduit[$i] || !$listDesignation[$i] ||!$listQte[$i] ||!$listUnite[$i] ){
                    $checkDataDetails = false;
                }
            }
    
            $respObjects["data"] = $listIdProduit;
    
            if (!$checkData || !$checkDataDetails) {
                $codeStatut = 'ERREUR-PARAMS-VIDE';
            } else {
                if(count($listIdProduit) > 0 ){
                    $this->blService->addBl( $data, $dataDetails);
                    $codeStatut = "OK";
                }else{
                    $codeStatut = 'ERREUR-PARAMS-VIDE';
                }
            }
        }else{
            $codeStatut = 'ERREUR-PARAMS-VIDE';
        }


        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->messageService->checkMessage($codeStatut);

        return $this->json($respObjects);
    }

    #[Route('/bl/update/{id}', name: 'update_bl')]
    public function updateBl($id): Response
    {
        $bl = $this->blService->getBl($id);

        $list = $this->EntityService->getListEntity();

        return $this->render('expedition/bl/updateBl.html.twig', [
            'bl' => $bl,
            'id' => $id,
            'listEntity'=>$list
        ]);
    }

    #[Route('/bl/ajaxUpdateBl/{id}', name: 'ajax_update_bl')]
    public function ajaxUpdateBl(Request $request, $id): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $bl = $this->blService->getBl($id);
        $data = [
            'date' => $request->get('date'),
            'code' => $request->get('code'),
            'entity' => $request->get('entity'),
        ];

        $checkData = $this->blService->checkData($data);
        $listIdProduit = $request->get('idProduit');
        $listDesignation = $request->get('designation');
        $listQte = $request->get('qte');
        $listUnite = $request->get('unite');
        $dataDetails = [];

        $checkDataDetails = true;
        if(isset($listIdProduit)){

            for ($i=0; $i < count($listIdProduit); $i++) { 
                $dataDetails[$i]['idProduit'] = $listIdProduit[$i];
                $dataDetails[$i]['designation'] = $listDesignation[$i];
                $dataDetails[$i]['qte'] = $listQte[$i];
                $dataDetails[$i]['unite'] = $listUnite[$i];
                if(!$listIdProduit[$i] || !$listDesignation[$i] ||!$listQte[$i] ||!$listUnite[$i] ){
                    $checkDataDetails = false;
                }
            }
    
            $respObjects["data"] = $listIdProduit;
    
            if (!$checkData || !$checkDataDetails) {
                $codeStatut = 'ERREUR-PARAMS-VIDE';
            } else {
                if(count($listIdProduit) > 0 ){
                    $this->blService->saveBl( $data, $dataDetails,$bl);
                    $codeStatut = "OK";
                }else{
                    $codeStatut = 'ERREUR-PARAMS-VIDE';
                }
            }
        }else{
            $codeStatut = 'ERREUR-PARAMS-VIDE';
        }

        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->messageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/bl/delete/{id}', name: 'ajax_delete_bl')]
    public function ajaxDeleteBl($id): Response
    {
        $respObjects = [];
        $codeStatut = "";

        $bl = $this->blService->getBl($id);

        if ($bl) {
            $this->blService->deleteBl($bl);
            $codeStatut = "OK";
        } else {
            $codeStatut = "ERREUR-BL-INEXISTANT";
        }

        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->messageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/bl/getDetailsBl/{id}', name: 'getDetailsBl')]
    public function getDetailsBl($id): Response
    {
        $respObjects = [];
        $codeStatut = "";


        $data = $this->blService->getDetailsBl($id);
        $respObjects["data"] = $data;

        $codeStatut = "OK";

        $respObjects["codeStatut"] = $codeStatut;
        $respObjects["message"] = $this->messageService->checkMessage($codeStatut);
        return $this->json($respObjects);
    }

    #[Route('/bl/pdf/{id}/', name: 'pdfBl')]
    public function pdfBl( $id)
    {
        
        $bl = $this->blService->getBl($id);

        $listBl = $this->blService->getDetailsBl($id);

        $totalBl = 0;

        foreach ($listBl as $item) {
            $totalBl += $item->getQte();
        }
        
        // Render the view
        $htmlContent = $this->renderView('/expedition/bl/pdfBl.html.twig', [
          "bl"=>$bl,
          "listBl"=>$listBl   ,
          'totalBl'=>$totalBl
        ]);
        $html2pdf = new Html2Pdf();
        $html2pdf->writeHTML($htmlContent);
        $pdfOutput = $html2pdf->output();
        $response = new Response($pdfOutput);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'inline; filename=bon_commande.pdf');

        return $response;

    }
}
