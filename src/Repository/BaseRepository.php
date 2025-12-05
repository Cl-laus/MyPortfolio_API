<?php
namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

abstract class BaseRepository extends ServiceEntityRepository
{
    public function save(object $entity): void
    {
        $this->getEntityManager()->persist($entity);

        $this->getEntityManager()->flush();

    }

    public function delete(object $entity): void
    {
        $this->getEntityManager()->remove($entity);

        $this->getEntityManager()->flush();

    }
}
