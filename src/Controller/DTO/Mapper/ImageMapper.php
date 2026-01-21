<?php
namespace App\Controller\DTO\Mapper;

use App\Controller\DTO\CreateImageDTO;
use App\Entity\Image;
use App\Entity\Project;

/**
 * Mapper pour convertir les DTO reçus en entités Image
 */
class ImageMapper
{
    /**
     * Crée une nouvelle entité Image à partir d'un DTO
     * 
     * @param Project $project Projet associé
     * @param CreateImageDTO $dto DTO validé par le controller
     * @param int $displayOrder Ordre d'affichage de l'image
     * @param string $url URL du fichier uploadé
     * @return Image
     */
    public function createFromDto(CreateImageDTO $dto, Project $project, int $displayOrder, string $url): Image
    {
        $image = new Image();
        $image
            ->setProject($project)
            ->setDisplayOrder($displayOrder)
            ->setUrl($url);

        return $image;
    }

}
