<?php

namespace App\Service;

use App\Repository\AchatGeneralRepository;
use App\Repository\FournisseurRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

class DashboardService
{
    public function __construct(
        private EntityManagerInterface $em, 
        private Connection $conn,  
        private FournisseurRepository $fournisseurRepo,
        private AchatGeneralRepository $achatGeneralRepository
    ) {}

    public function getDataFournisseurByYear(int $year): array
    {
        $fournisseurs = $this->fournisseurRepo->findAll();
        $currentMonth = 12; // To display all months of the selected year

        $result = [];
        foreach ($fournisseurs as $fournisseur) {
            $monthlyTotals = [];
            $totalGeneral = 0;

            for ($month = 1; $month <= $currentMonth; $month++) {
                $startDate = new \DateTime("$year-$month-01");
                $endDate = (clone $startDate)->modify('last day of this month');

                $query = $this->conn->prepare('
                    SELECT SUM(montant) AS total
                    FROM achat_general
                    WHERE id_fournisseur_id = :fournisseurId
                    AND date BETWEEN :startDate AND :endDate
                ');
                $query->execute([
                    'fournisseurId' => $fournisseur->getId(),
                    'startDate' => $startDate->format('Y-m-d'),
                    'endDate' => $endDate->format('Y-m-d'),
                ]);
                
                $res = $query->executeQuery(); $resultMonth = $res->fetchOne();
                $monthlyTotals[$month] = $resultMonth ? (float) $resultMonth : 0;
                $totalGeneral += $monthlyTotals[$month];
            }

            $result[] = [
                'rs' => $fournisseur->getRs(),
                'monthlyTotals' => $monthlyTotals,
                'totalGeneral' => $totalGeneral,
            ];
        }

        return $result;
    }

    public function achatByFournisseurAndMonthAndYear($month, $year, $fournisseurId)
    {
        return $this->achatGeneralRepository->findByMonthYearAndFournisseur($month, $year, $fournisseurId);
    }

}
