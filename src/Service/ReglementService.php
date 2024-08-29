<?php

namespace App\Service;

use App\Entity\Reglement;
use Doctrine\ORM\EntityManagerInterface;

class ReglementService
{
    private EntityManagerInterface $entityManager;
    private FournisseurService $FournisseurService;
    private EntityService $EntityService;

    public function __construct(EntityManagerInterface $entityManager, FournisseurService $FournisseurService, EntityService $EntityService)
    {
        $this->entityManager = $entityManager;
        $this->FournisseurService = $FournisseurService;
        $this->EntityService = $EntityService;
    }

    public function getListReglement(): array
    {
        return $this->entityManager->getRepository(Reglement::class)->findAll();
    }

    public function getReglement(int $id): ?Reglement
    {
        return $this->entityManager->getRepository(Reglement::class)->find($id);
    }

    public function addReglement(array $data): Reglement
    {
        $cheque = new Reglement();
        $cheque->setNumero($data['numero']);
        $cheque->setDate($data['date']);

        $fournisseur = $this->FournisseurService->getFournisseur($data['destinataire']);
        $cheque->setDestinataire($fournisseur);
        $cheque->setNumeroFacture($data['numeroFacture']);
        $cheque->setDetails($data['details']);
        $cheque->setMontant($data['montant']);
        $cheque->setDateEncaissement($data['date_encaissement']);
        $cheque->setBanque($data['banque']);

        $entity = $this->EntityService->getEntity($data['entity']);
        $cheque->setIdEntity($entity);

        $this->entityManager->persist($cheque);
        $this->entityManager->flush();

        return $cheque;
    }

    public function saveReglement(array $data, Reglement $cheque): Reglement
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

    public function deleteReglement(Reglement $cheque): void
    {
        $this->entityManager->remove($cheque);
        $this->entityManager->flush();
    }

    public function validateReglement(Reglement $cheque): void
    {
        $cheque->setEtat(1);
        $this->entityManager->flush();
    }

    public function getReglementByFournisseur(int $idFournisseur): array
    {
        return $this->entityManager->getRepository(Reglement::class)->findBy(['fournisseur' => $idFournisseur]);
    }

    public function checkData(array $data): bool
    {
        // Check if all required fields are present and valid
        foreach (['numero', 'date', 'destinataire', 'numeroFacture', 'montant', 'date_encaissement', 'banque','entity'] as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }
        return true;
    }
}
