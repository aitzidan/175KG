<?php

namespace App\Service;

use App\Entity\Tpe;
use App\Repository\TpeRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

class TpeService
{
    private $em;
    private $tpeRepo;
    private $conn;

    public function __construct(EntityManagerInterface $em, Connection $conn, TpeRepository $tpeRepo)
    {
        $this->em = $em;
        $this->tpeRepo = $tpeRepo;
        $this->conn = $conn;
    }

    public function checkData($data): bool
    {
        foreach (['date', 'tpe_caisse', 'tpe_naps', 'ecart'] as $field) {
            if (empty($data[$field])) {
                if($data[$field] != 0){
                    return false;
                }
            }
        }

        if (!is_numeric($data['tpe_caisse']) || !is_numeric($data['tpe_naps']) || !is_numeric($data['ecart'])) {
            return false;
        }

        return true;
    }

    public function addTpe($data): Tpe
    {
        $tpe = new Tpe();
        $tpe->setDateCreation(new \DateTime('now'));

        $tpe->setTpeCaisse($data['tpe_caisse']);
        $tpe->setTpeNaps($data['tpe_naps']);
        $tpe->setEcart($data['ecart']);
        $tpe->setDate(new \DateTime($data['date']));

        $this->em->persist($tpe);

        $this->em->flush();

        return $tpe;
    }

    public function saveTpe($data, ?Tpe $tpe = null): Tpe
    {
        if ($tpe === null) {
            $tpe = new Tpe();
            $tpe->setDateCreation(new \DateTime('now'));
        }

        $tpe->setTpeCaisse($data['tpe_caisse']);
        $tpe->setTpeNaps($data['tpe_naps']);
        $tpe->setEcart($data['ecart']);
        $tpe->setDate(new \DateTime($data['date']));

        if ($this->em->getRepository(Tpe::class)->find($tpe->getId()) === null) {
            $this->em->persist($tpe);
        }

        $this->em->flush();

        return $tpe;
    }


    public function getDataByFilter($filterType, $dateDebut, $dateFin, $annee, $mois)
    {
        $sql = 'SELECT t.* FROM tpe t';

        if ($filterType === 'date') {
            $dateDebut = $dateDebut . " 00:00:00";
            $dateFin = $dateFin . " 23:59:59";
            $sql .= ' WHERE t.date BETWEEN :dateDebut AND :dateFin';
        }

        if ($filterType === 'year') {
            $sql .= ' WHERE YEAR(t.date) = :annee';
            if ($mois) {
                $sql .= ' AND MONTH(t.date) = :mois';
            }
        }

        $stmt = $this->conn->prepare($sql);

        if ($filterType === 'date') {
            $stmt->bindValue('dateDebut', $dateDebut);
            $stmt->bindValue('dateFin', $dateFin);
        }

        if ($filterType === 'year') {
            $stmt->bindValue('annee', $annee);
            if ($mois) {
                $stmt->bindValue('mois', $mois);
            }
        }

        $stmt = $stmt->executeQuery();
        $result = $stmt->fetchAll();
        return $result;
    }

    public function getTpe($filterType, $dateDebut, $dateFin, $annee, $mois)
    {
        $sql = 'SELECT SUM(t.tpe_caisse) AS tpe_caisse_total, SUM(t.tpe_naps) AS tpe_naps_total FROM tpe t';

        if ($filterType === 'date') {
            $dateDebut = $dateDebut . " 00:00:00";
            $dateFin = $dateFin . " 23:59:59";
            $sql .= ' WHERE t.date BETWEEN :dateDebut AND :dateFin';
        }

        if ($filterType === 'year') {
            $sql .= ' WHERE YEAR(t.date) = :annee';
            if ($mois) {
                $sql .= ' AND MONTH(t.date) = :mois';
            }
        }

        $stmt = $this->conn->prepare($sql);

        if ($filterType === 'date') {
            $stmt->bindValue('dateDebut', $dateDebut);
            $stmt->bindValue('dateFin', $dateFin);
        }

        if ($filterType === 'year') {
            $stmt->bindValue('annee', $annee);
            if ($mois) {
                $stmt->bindValue('mois', $mois);
            }
        }

        $stmt = $stmt->executeQuery();
        $result = $stmt->fetchOne();
        return $result;
    }

    public function getTpeNaps($filterType, $dateDebut, $dateFin, $annee, $mois)
    {
        $sql = 'SELECT SUM(t.tpe_naps) AS tpe_caisse_total, SUM(t.tpe_naps) AS tpe_naps_total FROM tpe t';

        if ($filterType === 'date') {
            $dateDebut = $dateDebut . " 00:00:00";
            $dateFin = $dateFin . " 23:59:59";
            $sql .= ' WHERE t.date BETWEEN :dateDebut AND :dateFin';
        }

        if ($filterType === 'year') {
            $sql .= ' WHERE YEAR(t.date) = :annee';
            if ($mois) {
                $sql .= ' AND MONTH(t.date) = :mois';
            }
        }

        $stmt = $this->conn->prepare($sql);

        if ($filterType === 'date') {
            $stmt->bindValue('dateDebut', $dateDebut);
            $stmt->bindValue('dateFin', $dateFin);
        }

        if ($filterType === 'year') {
            $stmt->bindValue('annee', $annee);
            if ($mois) {
                $stmt->bindValue('mois', $mois);
            }
        }

        $stmt = $stmt->executeQuery();
        $result = $stmt->fetchOne();
        return $result;
    }


    public function getTpeByDateRange($dateDebut, $dateFin)
    {
        $sql = 'SELECT t.* FROM tpe t';
        $dateDebut = $dateDebut . " 00:00:00";
        $dateFin = $dateFin . " 23:59:59";
        $sql .= ' WHERE t.date BETWEEN :dateDebut AND :dateFin';

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue('dateDebut', $dateDebut);
        $stmt->bindValue('dateFin', $dateFin);

        $stmt = $stmt->executeQuery();
        $result = $stmt->fetchAll();
        return $result;
    }

    public function getTpeSummaryByDateRange($dateDebut, $dateFin)
    {
        $sql = 'SELECT SUM(t.tpe_caisse) AS tpe_caisse_total, SUM(t.tpe_naps) AS tpe_naps_total FROM tpe t';
        $dateDebut = $dateDebut . " 00:00:00";
        $dateFin = $dateFin . " 23:59:59";
        $sql .= ' WHERE t.date BETWEEN :dateDebut AND :dateFin';

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue('dateDebut', $dateDebut);
        $stmt->bindValue('dateFin', $dateFin);

        $stmt = $stmt->executeQuery();
        $result = $stmt->fetchOne();
        return $result;
    }
    public function getTpeNapsSummaryByDateRange($dateDebut, $dateFin)
    {
        $sql = 'SELECT SUM(t.tpe_naps) AS tpe_caisse_total, SUM(t.tpe_naps) AS tpe_naps_total FROM tpe t';
        $dateDebut = $dateDebut . " 00:00:00";
        $dateFin = $dateFin . " 23:59:59";
        $sql .= ' WHERE t.date BETWEEN :dateDebut AND :dateFin';

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue('dateDebut', $dateDebut);
        $stmt->bindValue('dateFin', $dateFin);

        $stmt = $stmt->executeQuery();
        $result = $stmt->fetchOne();
        return $result;
    }

    public function getTpeById($id)
    {
        return $this->tpeRepo->find($id);
    }

    public function deleteTpe($tpe){
        $this->em->remove($tpe);
        $this->em->flush();
    }
}
