<?php

namespace App\Service;

use App\Entity\Cheque;
use Doctrine\ORM\EntityManagerInterface;

class ChequeService
{
    private EntityManagerInterface $entityManager;
    private FournisseurService $FournisseurService;

    public function __construct(EntityManagerInterface $entityManager, FournisseurService $FournisseurService)
    {
        $this->entityManager = $entityManager;
        $this->FournisseurService = $FournisseurService;
    }

    public function getListCheque(): array
    {
        return $this->entityManager->getRepository(Cheque::class)->findAll();
    }

    public function getCheque(int $id): ?Cheque
    {
        return $this->entityManager->getRepository(Cheque::class)->find($id);
    }

    public function addCheque(array $data): Cheque
    {
        $cheque = new Cheque();
        $cheque->setNumero($data['numero']);
        $cheque->setDate($data['date']);

        $fournisseur = $this->FournisseurService->getFournisseur($data['destinataire']);
        $cheque->setDestinataire($fournisseur);
        $cheque->setNumeroFacture($data['numeroFacture']);
        $cheque->setDetails($data['details']);
        $cheque->setMontant($data['montant']);
        $cheque->setDateEncaissement($data['date_encaissement']);
        $cheque->setEtat(0);

        $this->entityManager->persist($cheque);
        $this->entityManager->flush();

        return $cheque;
    }

    public function saveCheque(array $data, Cheque $cheque): Cheque
    {
        $cheque->setNumero($data['numero']);
        $cheque->setDate($data['date']);
        
        $fournisseur = $this->FournisseurService->getFournisseur($data['destinataire']);
        $cheque->setDestinataire($fournisseur);
        $cheque->setNumeroFacture($data['numeroFacture']);
        $cheque->setDetails($data['details']);
        $cheque->setMontant($data['montant']);
        $cheque->setDateEncaissement($data['date_encaissement']);

        $this->entityManager->flush();

        return $cheque;
    }

    public function deleteCheque(Cheque $cheque): void
    {
        $this->entityManager->remove($cheque);
        $this->entityManager->flush();
    }

    public function validateCheque(Cheque $cheque): void
    {
        $cheque->setEtat(1);
        $this->entityManager->flush();
    }

    public function getChequeByFournisseur(int $idFournisseur): array
    {
        return $this->entityManager->getRepository(Cheque::class)->findBy(['fournisseur' => $idFournisseur]);
    }

    public function checkData(array $data): bool
    {
        // Check if all required fields are present and valid
        foreach (['numero', 'date', 'destinataire', 'numeroFacture', 'montant', 'date_encaissement'] as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }
        return true;
    }
    public function save(){
        $this->entityManager->flush();
    }
}
