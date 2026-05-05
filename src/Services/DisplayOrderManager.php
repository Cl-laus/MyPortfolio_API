<?php
namespace App\Services;

use Doctrine\ORM\EntityManagerInterface;

final class DisplayOrderManager
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    /**
     * Réorganise les éléments quand un élément change de position
     *
     * @param iterable $elements collection d'entités avec getDisplayOrder() et setDisplayOrder()
     * @param int $oldOrder
     * @param int $newOrder
     */
    public function reOrder(iterable $elements, int $oldOrder, int $newOrder): void
    {
        foreach ($elements as $e) {
            $currentOrder = $e->getDisplayOrder();
            // Cas 1 : Projet déplacé vers le BAS (ex: 1 → 3)
            // Les projets entre 2 et 3 remontent d'une position
            if ($newOrder > $oldOrder && $currentOrder > $oldOrder && $currentOrder <= $newOrder) {
                $e->setDisplayOrder($currentOrder - 1);
                // Cas 2 : Projet déplacé vers le HAUT (ex: 3 → 1)
                // Les projets entre 1 et 2 descendent d'une position
            } elseif ($newOrder < $oldOrder && $currentOrder >= $newOrder && $currentOrder < $oldOrder) {
                $e->setDisplayOrder($currentOrder + 1);
            }

            $this->em->persist($e);
        }

        $this->em->flush();
    }

    /**
     * Comble le trou laissé par un élément supprimé
     *
     * @param iterable $elements Entité avec getDisplayOrder() et setDisplayOrder()
     * @param int $deletedOrder

     */
    public function fillGapAfterDeletion(iterable $elements, int $deletedOrder): void
    {
        foreach ($elements as $e) {
            if ($e->getDisplayOrder() > $deletedOrder) {
                $e->setDisplayOrder($e->getDisplayOrder() - 1);
                $this->em->persist($e);
            }
        }

        $this->em->flush();
    }

    /**
     * Valide que l'ordre est correct
     */
    public function validateDisplayOrder(int $order, int $maxOrder): void
    {
        if ($order < 1) {
            throw new \DomainException('L\'ordre doit être supérieur ou égal à 1.');
        }
        if ($order > $maxOrder) {
            throw new \DomainException("L'ordre ne peut pas dépasser {$maxOrder}.");
        }
    }
}
