<?php

namespace App\Controller\Dashboard;

use App\Service\AchatGeneralService;
use App\Service\BaseService;
use App\Service\BlService;
use App\Service\CaisseParamService;
use App\Service\CaisseService;
use App\Service\DashboardService;
use App\Service\EntityService;
use App\Service\FournisseurService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends AbstractController
{
    public function __construct(private EntityService $EntityService,private CaisseParamService $CaisseParamService,private DashboardService $DashboardService, private FournisseurService $FournisseurService,
    private CaisseService $CaisseService,
    private AchatGeneralService $AchatGeneralService ,
    private BaseService $BaseService ,
    private BlService $BlService 
    ) {}

    #[Route('/dashboard', name: "tableauBoard")]
    public function index(Request $request): Response
    {
        $chckAccess = $this->BaseService->Role(123);
        if ($chckAccess == 0) {
            return $this->redirectToRoute('login');
        } elseif ($chckAccess == 2) {
            return $this->redirectToRoute('listUsers');
        }
    
        $caisseList = $this->CaisseParamService->getListCaisse();
        $caisseSelect = "";

        $entityList = $this->EntityService->getListEntity();
        $currentYear = (new \DateTime())->format('Y');
        $years = range($currentYear - 5, $currentYear); // Example range from 5 years ago to the current year
    
        $currentDate = new \DateTime();
        $currentDate->modify('first day of this month');
        $date_debut = $currentDate->format('Y-m-d');
        $currentDate->modify('last day of this month');
        $date_fin = $currentDate->format('Y-m-d');
    
        $filterType = 'date';
        $fournisseur = '';
    
        // Get the current year and month
        $currentYear = (int) $currentDate->format('Y');
        $currentMonth = (int) $currentDate->format('n'); // Month as a number (1-12)
    
        // List of months from January to the current month
        $listMonths = [];
        for ($month = 1; $month <= $currentMonth; $month++) {
            $listMonths[] = [
                'number' => $month,
                'name' => $this->BaseService->formatMonth($month) // Full month name
            ];
        }
    
        $listAllMonths = [];
        for ($month = 1; $month <= 12; $month++) {
            $listAllMonths[] = [
                'number' => $month,
                'name' => $this->BaseService->formatMonth($month) // Full month name
            ];
        }
    
        $listYears = [];
        for ($year = 2020; $year <= $currentYear; $year++) {
            $listYears[] = $year;
        }
    
        if ($request->isMethod('POST')) {
            // Process form data here
            $filterType = $request->request->get('filter_type');
            $date_debut = $request->request->get('date_debut');
            $date_fin = $request->request->get('date_fin');
            $annee = $request->request->get('annee');
            $mois = $request->request->get('mois');
            $caisseSelect = $request->request->get('caisseSelect'); // Use request->request for POST data
    
            $list = $this->CaisseService->getDataByFilterDash($filterType, $date_debut, $date_fin, $annee, $mois, $caisseSelect);
            $Espece = $this->CaisseService->getTheEspece($filterType, $date_debut, $date_fin, $annee, $mois , $caisseSelect) ?? 0;
            $TPE = $this->CaisseService->getTpe($filterType, $date_debut, $date_fin, $annee, $mois , $caisseSelect) ?? 0;
            $ECART = $this->CaisseService->getEcart($filterType, $date_debut, $date_fin, $annee, $mois , $caisseSelect) ?? 0;

        } else {
            $list = $this->CaisseService->getDataByFilter2Dash($date_debut, $date_fin);
            $Espece = $this->CaisseService->getTheEspece2($date_debut, $date_fin) ?? 0;
            $TPE = $this->CaisseService->getTpe2($date_debut, $date_fin) ?? 0;
            $ECART = $this->CaisseService->getEcart2($date_debut, $date_fin) ?? 0;
        }
    
        return $this->render('dashboard/dashboard.html.twig', [
            'controller_name' => 'DashboardController',
            'currentYear' => $currentYear,
            'years' => $years,
            'date_debut' => $date_debut,
            'date_fin' => $date_fin,
            'list_years' => $listYears,
            'list_months' => $listMonths,
            'list_all_months' => $listAllMonths,
            'filter_type' => $filterType,
            'fournisseur' => $fournisseur,
            'caisse' => $list,
            'TPE' => $TPE,
            'Espece' => $Espece,
            'ECART' => $ECART,
            'caisseList' => $caisseList,
            'entityList'=>$entityList,
            "caisseSelect"=>$caisseSelect
        ]);
    }
    
    #[Route('/api/getDataFournisseur', name: 'api_getDataFournisseur')]
    public function apiGetDataFournisseur(): JsonResponse
    {
        $year = (int) $_GET['year'] ?? (new \DateTime())->format('Y');
        $data = $this->DashboardService->getDataFournisseurByYear($year);
        return new JsonResponse($data);
    }

    #[Route('/dashboard/achat/{month}/{year}/{idFournisseur}')]
    public function achatByFournisseurAndMonthAndYear($month , $year , $idFournisseur): Response
    {
        $data = $this->DashboardService->achatByFournisseurAndMonthAndYear($month , $year , $idFournisseur);
        $fournisseur = $this->FournisseurService->getFournisseur($idFournisseur);

        return $this->render('dashboard/achat.html.twig', [
            "data" =>$data ,
            "fournisseur" =>$fournisseur ,
        ]);
    }

    #[Route('/dashboard/filter', name: 'ajax_filter_dashboard')]
    public function filterDashboard(Request $request): JsonResponse
    {
        $filterType = $request->request->get('filter_type');
        $date_debut = $request->request->get('date_debut');
        $date_fin = $request->request->get('date_fin');
        $annee = $request->request->get('annee');
        $mois = $request->request->get('mois');
        $fournisseur = $request->request->get('fournisseur');

        $list = $this->AchatGeneralService->getDateByFiltrage($filterType, $date_debut, $date_fin, $annee, $mois, $fournisseur);
        $montant = $this->AchatGeneralService->getMontant($filterType, $date_debut, $date_fin, $annee, $mois, $fournisseur) ?? 0;

        $dataList = [];

        foreach ($list as $i => $item) {
            $dataList[$i] = $item;
            $dataList[$i]['categorie_id'] = $this->AchatGeneralService->getOneCategorie($item['categorie_id'])->getCategorie();
            $dataList[$i]['id_fournisseur_id'] = $this->AchatGeneralService->getOneFournisseurs($item['id_fournisseur_id'])->getRs();
            $dataList[$i]['designation'] = $this->AchatGeneralService->getOneDesignation($item['id_designation_id'])->getDesignation();
        }

        // Render the partial view
        $html = $this->renderView('dashboard/_dashboard_results.html.twig', [
            'achat' => $dataList,
            'montant' => $montant,
        ]);

        return new JsonResponse(['html' => $html]);
    }

    #[Route('/dashboard/exportDataAchat', name: 'export_achat')]
    public function exportCaisseDashboard(Request $request): Response
    {
        // Start output buffering
        ob_start();
    
        $filterType = $request->query->get('filter_type');
        $date_debut = $request->query->get('date_debut');
        $date_fin = $request->query->get('date_fin');
        $annee = $request->query->get('annee');
        $mois = $request->query->get('mois');
        $fournisseur = $request->query->get('fournisseur');
    
        // Fetch filtered data
        $list = $this->AchatGeneralService->getDateByFiltrage($filterType, $date_debut, $date_fin, $annee, $mois, $fournisseur);
        $montant = $this->AchatGeneralService->getMontant($filterType, $date_debut, $date_fin, $annee, $mois, $fournisseur) ?? 0;
    
        $dataList = [];
    
        foreach ($list as $i => $item) {
            $dataList[$i] = $item;
            $dataList[$i]['categorie_id'] = $this->AchatGeneralService->getOneCategorie($item['categorie_id'])->getCategorie();
            $dataList[$i]['id_fournisseur_id'] = $this->AchatGeneralService->getOneFournisseurs($item['id_fournisseur_id'])->getRs();
            $dataList[$i]['designation'] = $this->AchatGeneralService->getOneDesignation($item['id_designation_id'])->getDesignation();
        }
    
        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        // Set header
        $headers = ['Date', 'Categorie', 'Fournisseur', 'Designation', 'Montant'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }
    
        // Add data
        $row = 2; // Start from row 2
        foreach ($dataList as $item) {
            $dateItem = new \Datetime($item['date']);

            $sheet->setCellValue('A' . $row, $dateItem->format('d-m-Y'));
            $sheet->setCellValue('B' . $row, $item['categorie_id']);
            $sheet->setCellValue('C' . $row, $item['id_fournisseur_id']);
            $sheet->setCellValue('D' . $row, $item['designation']);
            $sheet->setCellValue('E' . $row, $item['montant']);
            $row++;
        }
    
        // Create Excel writer
        $writer = new Xlsx($spreadsheet);
        $fileName = 'achat_data.xlsx';
    
        // Create response
        $response = new Response();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->headers->set('Cache-Control', 'max-age=0');
    
        // Start output buffering
        ob_start();
        // Save the file to output
        $writer->save('php://output');
        // End buffering and get contents
        $content = ob_get_clean();
    
        // Send the response
        $response->setContent($content);
        return $response;
    }
    

    #[Route('/dashboard/exportFournisseur/{year}', name: 'export_fournisseur')]
    public function exportFournisseurData(int $year): Response
    {
        // Fetch the data
        $data = $this->DashboardService->getDataFournisseurByYear($year);
    
        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Fournisseurs');
    
        // Set header
        $headers = array_merge(['Fournisseur', 'ID'], array_map(function($month) {
            return \DateTime::createFromFormat('!m', $month)->format('F');
        }, range(1, 12)), ['Total General']);
    
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }
    
        // Add data
        $row = 2;
        foreach ($data as $fournisseur) {
            $sheet->setCellValue('A' . $row, $fournisseur['rs']);
            $sheet->setCellValue('B' . $row, $fournisseur['id']);
    
            $col = 'C';
            foreach ($fournisseur['monthlyTotals'] as $monthTotal) {
                $sheet->setCellValue($col . $row, $monthTotal);
                $col++;
            }
    
            $sheet->setCellValue($col . $row, $fournisseur['totalGeneral']);
            $row++;
        }
    
        // Create Excel writer
        $writer = new Xlsx($spreadsheet);
        $fileName = 'fournisseurs_' . $year . '.xlsx';
    
        // Create response
        $response = new Response();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->headers->set('Cache-Control', 'max-age=0');
    
        // Start output buffering
        ob_start();
        // Save the file to output
        $writer->save('php://output');
        // End buffering and get contents
        $content = ob_get_clean();
    
        // Send the response
        $response->setContent($content);
        return $response;
    }


    #[Route('/dashboard/exportDataCaisse', name: 'export_caisse')]
    public function exportCaisse(Request $request): Response
    {
        // Retrieve parameters from the request
        $filterType = $request->query->get('filter_type');
        $date_debut = $request->query->get('date_debut');
        $date_fin = $request->query->get('date_fin');
        $annee = $request->query->get('annee');
        $mois = $request->query->get('mois');
        $caisse = $request->query->get('caisseSelect');
        
        // Fetch filtered data
        $list = $this->CaisseService->getDataByFilterDash($filterType, $date_debut, $date_fin, $annee, $mois, $caisse);
        $Espece = $this->CaisseService->getTheEspece($filterType, $date_debut, $date_fin, $annee, $mois,$caisse);
        $TPE = $this->CaisseService->getTpe($filterType, $date_debut, $date_fin, $annee, $mois,$caisse);
        $ECART = $this->CaisseService->getEcart($filterType, $date_debut, $date_fin, $annee, $mois,$caisse);

        $dataList = [];

        foreach ($list as $i => $item) {
            $dataList[$i] = $item;
            // You might need additional data fetching here
        }

        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header
        $headers = ['Date', 'TPE', 'TPE NAPS', 'Ecart', 'Espèce final'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        // Add data
        $row = 2; // Start from row 2
        foreach ($dataList as $item) {
            $dateItem = new \Datetime($item['date']);

            $sheet->setCellValue('A' . $row, $dateItem->format('d-m-Y'));
            $sheet->setCellValue('B' . $row, $item['tpe']);
            $sheet->setCellValue('C' . $row, $item['tpe_naps']);
            $sheet->setCellValue('D' . $row, $item['ecart']);
            $sheet->setCellValue('E' . $row, $item['espece_final']);
            $row++;
        }

        // Create Excel writer
        $writer = new Xlsx($spreadsheet);
        $fileName = 'caisse_data.xlsx';

      // Create response
      $response = new Response();
      $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
      $response->headers->set('Cache-Control', 'max-age=0');
  
      // Start output buffering
      ob_start();
      // Save the file to output
      $writer->save('php://output');
      // End buffering and get contents
      $content = ob_get_clean();
  
      // Send the response
      $response->setContent($content);
      return $response;
    }


    #[Route('/dashboard/filterBl', name: 'ajax_filter_dashboard_bl')]
    public function filterDashboardBl(Request $request): JsonResponse
    {
        $filterType = $request->request->get('filter_type');
        $date_debut = $request->request->get('date_debut');
        $date_fin = $request->request->get('date_fin');
        $annee = $request->request->get('annee');
        $mois = $request->request->get('mois');
        $entity = $request->request->get('entitySelect'); // Use request->request for POST data

        $dataList = $this->BlService->getDataBl($filterType, $date_debut, $date_fin, $annee, $mois, $entity)['list'];
        $montant = $this->BlService->getDataBl($filterType, $date_debut, $date_fin, $annee, $mois, $entity)['montant'];

        // Render the partial view
        $html = $this->renderView('dashboard/_dashboard_bl.html.twig', [
            'bl' => $dataList,
            'montant' => $montant,
        ]);

        return new JsonResponse(['html' => $html]);
    }

    #[Route('/dashboard/exportDataBl', name: 'export_bl')]
    public function exportBl(Request $request): Response
    {
        // Retrieve parameters from the request
        $filterType = $request->request->get('filter_type');
        $date_debut = $request->request->get('date_debut');
        $date_fin = $request->request->get('date_fin');
        $annee = $request->request->get('annee');
        $mois = $request->request->get('mois');
        $entity = $request->request->get('entitySelect'); // Use request->request for POST data

        $dataList = $this->BlService->getDataBl($filterType, $date_debut, $date_fin, $annee, $mois, $entity)['list'];
        $montant = $this->BlService->getDataBl($filterType, $date_debut, $date_fin, $annee, $mois, $entity)['montant'];


        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header
        $headers = ['Date', 'Numéro', 'Quantité'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }

        // Add data
        $row = 2; // Start from row 2
        foreach ($dataList as $item) {
            $dateItem = new \Datetime($item['date']);

            $sheet->setCellValue('A' . $row, $dateItem->format('d-m-Y'));
            $sheet->setCellValue('B' . $row, $item['code']);
            $sheet->setCellValue('C' . $row, $item['qte']);
            $row++;
        }

        // Create Excel writer
        $writer = new Xlsx($spreadsheet);
        $fileName = 'bl_data.xlsx';

      // Create response
      $response = new Response();
      $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
      $response->headers->set('Cache-Control', 'max-age=0');
  
      // Start output buffering
      ob_start();
      // Save the file to output
      $writer->save('php://output');
      // End buffering and get contents
      $content = ob_get_clean();
  
      // Send the response
      $response->setContent($content);
      return $response;
    }
}
