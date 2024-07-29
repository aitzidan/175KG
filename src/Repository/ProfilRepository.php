<?php

namespace App\Repository;

use App\Entity\Profil;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Role;

/**
 * @extends ServiceEntityRepository<Profil>
 *
 * @method Profil|null find($id, $lockMode = null, $lockVersion = null)
 * @method Profil|null findOneBy(array $criteria, array $orderBy = null)
 * @method Profil[]    findAll()
 * @method Profil[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProfilRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Profil::class);
    }

    public function insertProfil($profilData,$roles): void
    {
        $entityManager = $this->getEntityManager();

        // Create a new Profil instance
        $profil = new Profil();
        $profil->setNom($profilData['libelle']);
        $profil->setDescription($profilData['description']);
        $profil->setDateCreation(new \DateTime());

        // Fetch the Role entities from the database based on the selected roles
        $selectedRoles = [];
        foreach ($roles as $roleId => $isSelected) {
            if ($isSelected === "on") {
                $role = $entityManager->getRepository(Role::class)->find($roleId);
                if ($role) {
                    $selectedRoles[] = $role;
                }
            }
        }

        // Associate the fetched Role entities with the Profil
        foreach ($selectedRoles as $role) {
            $profil->addRole($role);
        }

        // Persist the Profil entity along with its associated Role entities
        $entityManager->persist($profil);
        $entityManager->flush();
    }

    public function updateProfil($id, $profilData, $roles): void
    {
        $entityManager = $this->getEntityManager();

        // Fetch the existing Profil instance
        $profil = $entityManager->getRepository(Profil::class)->find($id);

        if (!$profil) {
            throw new \Exception("Profil not found");
        }

        // Update the Profil properties
        $profil->setNom($profilData['libelle']);
        $profil->setDescription($profilData['description']);
        $profil->setDateUpdate(new \DateTime());

        // Clear existing roles
        foreach ($profil->getRoles() as $role) {
            $profil->removeRole($role);
        }

        // Fetch the Role entities from the database based on the selected roles
        $selectedRoles = [];
        foreach ($roles as $roleId => $isSelected) {
            if ($isSelected === "on") {
                $role = $entityManager->getRepository(Role::class)->find($roleId);
                if ($role) {
                    $selectedRoles[] = $role;
                }
            }
        }

        // Associate the fetched Role entities with the Profil
        foreach ($selectedRoles as $role) {
            $profil->addRole($role);
        }

        // Persist the updated Profil entity along with its associated Role entities
        $entityManager->flush();
    }


}
