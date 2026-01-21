<?php
namespace App\Repository;

use App\Entity\Image;
use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Image>
 */
class ImageRepository extends BaseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Image::class);

    }

    public function getMaxDisplayOrderForProject(Project $project): int
    {
        $result = $this->createQueryBuilder('i')
            ->select('MAX(i.displayOrder)')
            ->where('i.project = :project')
            ->setParameter('project', $project)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ?? 0;
    }
}
