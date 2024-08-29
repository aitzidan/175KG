<?php

namespace App\Controller\Dashboard;

use App\Service\DashboardService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    public function __construct(private DashboardService $DashboardService) {}

    #[Route('/dashboard')]
    public function index(): Response
    {
        $currentYear = (new \DateTime())->format('Y');
        $years = range($currentYear - 5, $currentYear); // Example range from 5 years ago to current year

        return $this->render('dashboard/dashboard.html.twig', [
            'controller_name' => 'DashboardController',
            'currentYear' => $currentYear,
            'years' => $years,
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
        dump($data);
        return $this->render('dashboard/achat.html.twig', [
            "data" =>$data 
        ]);
    }
}
