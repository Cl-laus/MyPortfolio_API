<?php
namespace App\Services;

use App\Repository\ImageRepository;

/**
 * Service pour gérer la logique métier liée aux projets
 * Gère la création, mise à jour, suppression et réorganisation des projets
 */
class ImageManager
{
    public function __construct(
        private ImageRepository $imageRepository,
        private DisplayOrderManager $displayOrderManager
    ) {}







    ////////// DELETE //////////

    public function delete(int $id): void
    {

        $image = $this->imageRepository->find($id);
        if (! $image) {
            throw new \DomainException('Image introuvable.');
        }
        $deletedOrder = $image->getDisplayOrder();
        $this->imageRepository->delete($image);
        //Combler le "trou" : décaler tous les projets après celui supprimé
        $this->displayOrderManager->fillGapAfterDeletion(
            $this->imageRepository->findAll(),
            $deletedOrder
        );
    }

}
