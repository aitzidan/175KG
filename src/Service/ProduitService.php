<?php

namespace App\Service;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProduitService
{
    private $em;
    private $produitRepo;

    public function __construct(EntityManagerInterface $em, ProduitRepository $produitRepo)
    {
        $this->em = $em;
        $this->produitRepo = $produitRepo;
    }

    public function checkData($data): bool
    {
        // Check if all fields are present and not empty
        foreach (['produit', 'unite'] as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }
        return true;
    }

    public function addProduit($data): Produit
    {
        $produit = new Produit();
        $produit->setCode($data['code'] ?? null);
        $produit->setProduit($data['produit']);
        $produit->setUnite($data['unite']);

        $this->em->persist($produit);
        $this->em->flush();

        return $produit;
    }

    public function saveProduit($data, ?Produit $produit = null): Produit
    {
        if ($produit === null) {
            $produit = new Produit();
        }
        $produit->setCode($data['code'] ?? null);
        $produit->setProduit($data['produit']);
        $produit->setUnite($data['unite']);

        $this->em->flush();

        return $produit;
    }

    public function getProduit($id): ?Produit
    {
        return $this->produitRepo->find($id);
    }

    public function deleteProduit(Produit $produit): void
    {
        $this->em->remove($produit);
        $this->em->flush();
    }

    public function getListProduit(): array
    {
        return $this->produitRepo->findAll();
    }
}
