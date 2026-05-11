<?php
namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

// Repository de base partagé par tous les repositories de l'application.
// Toutes les interactions avec la base de données passent par Doctrine ORM,
// qui génère des requêtes SQL préparées avec des paramètres liés.
// Cela rend les injections SQL impossibles : les données utilisateur
// ne sont jamais interpolées directement dans le SQL.
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
