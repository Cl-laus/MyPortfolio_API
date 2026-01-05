<?php
namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 */
class ProjectRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

/**
 * Récupère les 3 projets avec le plus petit displayOrder
 */
    public function findTop3Projects(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.displayOrder', 'ASC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    }
/**
 * Récupère le plus grand displayOrder existant
 * Retourne 0 si aucun projet n'existe
 */
    public function getMaxDisplayOrder(): int
    {
        $result = $this->createQueryBuilder('p')
            ->select('MAX(p.displayOrder)')
            ->getQuery()
            ->getSingleScalarResult();

        return $result ?? 0;
    }

/**
 * Trouve tous les projets ayant un displayOrder supérieur à l'ordre donné
 */
    public function findProjectsAfterOrder(int $order): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.displayOrder > :order')
            ->setParameter('order', $order)
            ->orderBy('p.displayOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }
    //    /**
    //     * @return Project[] Returns an array of Project objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Project
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
