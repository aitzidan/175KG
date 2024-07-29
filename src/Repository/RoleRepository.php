<?php

namespace App\Repository;

use App\Entity\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Role>
 *
 * @method Role|null find($id, $lockMode = null, $lockVersion = null)
 * @method Role|null findOneBy(array $criteria, array $orderBy = null)
 * @method Role[]    findAll()
 * @method Role[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }


    public function findAllGroupedByCategory(): array
    {
        // Fetch all roles
        $roles = $this->createQueryBuilder('r')
            ->getQuery()
            ->getResult();
    
        // Group roles by category
        $groupedRoles = [];
        foreach ($roles as $role) {
            $category = $role->getCategory();
            if (!isset($groupedRoles[$category])) {
                $groupedRoles[$category] = [];
            }
            $groupedRoles[$category][] = $role;
        }
    
        return $groupedRoles;
    }
    


}
