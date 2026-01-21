<?php
namespace App\Services;

use App\Entity\Image;
use App\Repository\ImageRepository;
use App\Repository\ProjectRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Service pour gérer la logique métier liée aux images
 * Gère la création, mise à jour, suppression et réorganisation des images
 */
class ImageManager
{
    public function __construct(
        private ImageRepository $imageRepository,
        private ProjectRepository $projectRepository,
        private DisplayOrderManager $displayOrderManager,
        private ImageUploadService $uploadService
    ) {}

    ////////// CREATE MULTIPLE //////////

    /**
     * Ajouter plusieurs images pour un projet
     * Le displayOrder est automatiquement défini par la position après les images existantes
     *
     * @param int $projectId
     * @param array $files Tableau de files recupérées via Request
     * @return Image[]
     */
    public function createMultiple(int $projectId, array $files): array
    {
        // 1. Validation : au moins 1 fichier
        if (empty($files)) {
            throw new \DomainException('Aucune image fournie.');
        }

        // 2. Validation : maximum 10 fichiers
        if (count($files) > 10) {
            throw new \DomainException('Maximum 10 images par requête.');
        }

        // 3. Vérifier que le projet existe via son ID
        $project = $this->projectRepository->find($projectId);
        if (! $project) {
            throw new \DomainException('Projet introuvable.');
        }

        // 4. Valider chaque fichier
        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                throw new \DomainException('Fichier invalide détecté.');
            }

            if (! $file->isValid()) {
                throw new \DomainException('Erreur lors de l\'upload du fichier.');
            }

            // Vérifier le type MIME
            // if ($file->getMimeType() !== 'image/webp') {
            //     throw new \DomainException('Tous les fichiers doivent être au format WebP.');
            // }

            // Vérifier la taille (5MB max)
            // if ($file->getSize() > 5 * 1024 * 1024) {
            //     throw new \DomainException('La taille maximale par fichier est de 5MB.');
            // }
        }

        // 5. Récupérer le displayOrder maximum existant pour ce projet(converti en int)
        $maxOrder = (int) $this->imageRepository->getMaxDisplayOrderForProject($project);

        // 6. Créer chaque image avec displayOrder incrémental
        $createdImages = [];
        $displayOrder  = $maxOrder;
        foreach ($files as $file) {
            $displayOrder++;

            $url = $this->uploadService->upload($file, $project, $displayOrder);

            $image = new Image();
            $image
                ->setProject($project)
                ->setDisplayOrder($displayOrder)
                ->setUrl($url);

            $this->imageRepository->save($image);

            $createdImages[] = $image;
        }
        return $createdImages;
    }

    ////////// UPDATE (seulement displayOrder) //////////

    /**
     * Met à jour uniquement le displayOrder d'une image
     * Réorganise automatiquement les autres images du projet
     */
    public function updateDisplayOrder(int $imageId, int $newDisplayOrder): Image
    {
        // 1. Récupérer l'image
        $image = $this->imageRepository->find($imageId);
        if (! $image) {
            throw new \DomainException('Image introuvable.');
        }

        $project      = $image->getProject();
        $currentOrder = $image->getDisplayOrder();

        // 2. Si l'ordre change, réorganiser les autres images
        if ($currentOrder !== $newDisplayOrder) {
            $allImages = $this->imageRepository->findBy(['project' => $project]);

            // Valider et réorganiser les autres images
            $this->displayOrderManager->validateDisplayOrder($newDisplayOrder, count($allImages));
            $this->displayOrderManager->reOrder($allImages, $currentOrder, $newDisplayOrder);

            // Mettre à jour directement l'image
            $image->setDisplayOrder($newDisplayOrder);
            $this->imageRepository->save($image);
        }

        return $image;
    }

    ////////// DELETE //////////

    /**
     * Supprime une image et comble le trou dans les displayOrder
     */
    public function delete(int $id): void
    {
        // 1. Récupérer l'image existante ou erreur
        $image = $this->imageRepository->find($id);
        if (! $image) {
            throw new \DomainException('Image introuvable.');
        }

        // 2. Récupérer l'ordre avant suppression
        $deletedOrder = $image->getDisplayOrder();
        $project      = $image->getProject();

        // 3. Supprimer le fichier du disque
        $this->uploadService->delete($image->getUrl());

        // 4. Supprimer l'entité
        $this->imageRepository->delete($image);

        // 5. Combler le "trou" : décaler toutes les images après celle supprimée
        $projectImages = $this->imageRepository->findBy(['project' => $project]);
        $this->displayOrderManager->fillGapAfterDeletion($projectImages, $deletedOrder);
    }
}
