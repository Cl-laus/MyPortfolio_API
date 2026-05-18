<?php
namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

// Classe partagée par tous les repositories.
// Doctrine ORM génère des requêtes SQL préparées — les données utilisateur
// ne sont jamais injectées directement dans le SQL (protection contre les injections SQL).
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
