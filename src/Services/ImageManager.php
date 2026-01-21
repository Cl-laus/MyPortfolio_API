<?php
namespace App\Services;

use App\Controller\DTO\Mapper\ImageMapper;
use App\Entity\Image;
use App\Repository\ImageRepository;
use App\Repository\ProjectRepository;
use App\Controller\DTO\CreateImageDTO;


/**
 * Service pour gérer la logique métier liée aux projets
 * Gère la création, mise à jour, suppression et réorganisation des projets
 */
class ImageManager
{
    public function __construct(
        private ImageRepository $imageRepository,
        private ProjectRepository $projectRepository,
        private DisplayOrderManager $displayOrderManager,
        private ImageMapper $imageMapper,
        private ImageUploadService $uploadService
    ) {}


    ////////// CREATE MULTIPLE //////////

   /**
 * Crée plusieurs images pour un projet
 * Le displayOrder est automatiquement défini par la position après les images existantes
 *
 * @param int $projectId
 * @param CreateImageDTO[] $imagesDto Tableau de DTO validés
 * @return Image[]
 */

    public function createMultiple(int $projectId, array $imagesDto): array
    {
        // 1. Vérifier que le projet existe
        $project = $this->projectRepository->find($projectId);
        if (!$project) {
            throw new \DomainException('Projet introuvable.');
        }

        // 2. Récupérer le displayOrder maximum existant pour ce projet
        $maxOrder = $this->imageRepository->getMaxDisplayOrderForProject($project);

        $createdImages = [];

        foreach ($imagesDto as $index => $dto) {
            $displayOrder = $maxOrder + $index + 1;

            // 3. Upload du fichier et récupération de l'URL
            ////TO DO: activer l'upload
            $url = $this->uploadService->upload($dto->file, $project, $displayOrder);

            // 4. Création de l'entité via le mapper
            $image = $this->imageMapper->createFromDto($dto, $project, $displayOrder, $url);

            // 5. Persister l'image
            $this->imageRepository->save($image);

            $createdImages[] = $image;
        }

        return $createdImages;
    }


    ////////// UPDATE seulement displayOrder //////////
   
    public function updateDisplayOrder(int $imageId, int $newDisplayOrder): Image
    {
        $image = $this->imageRepository->find($imageId);
        if (!$image) {
            throw new \DomainException('Image introuvable.');
        }

        $project = $image->getProject();
        $currentOrder = $image->getDisplayOrder();

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
