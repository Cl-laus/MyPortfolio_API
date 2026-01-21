<?php
namespace App\Controller\DTO\Mapper;

use App\Controller\DTO\CreateProjectDTO;
use App\Controller\DTO\UpdateProjectDTO;
use App\Entity\Project;
use App\Entity\Technology;

/**
 * Mapper pour convertir les DTO recus en entités Project
 */

class ProjectMapper
{
    /**
     * Crée une nouvelle entité Project à partir d'un DTO
     * 
     * @param Technology[] $technologies Tableau d'entités Technology déjà validées par le ProjectManager
     */
    public function createFromDto(CreateProjectDTO $dto, array $technologies): Project
    {
        $project = new Project();
        $project
            ->setTitle($dto->title)
            ->setSummary($dto->summary)
            ->setDescription($dto->description)
            ->setLinks($dto->links ?? []);

        // Ajouter les technologies
        foreach ($technologies as $technology) {
            $project->addTechnology($technology);
        }
        return $project;
    }

    /**
     * Met à jour une entité Project existante à partir d'un DTO
     */
    public function updateFromDto(UpdateProjectDTO $dto, Project $project, ?array $technologies = null): Project
    {
        // Mise à jour des champs si non null
        if ($dto->title !== null) {
            $project->setTitle($dto->title);
        }

        if ($dto->summary !== null) {
            $project->setSummary($dto->summary);
        }

        if ($dto->description !== null) {
            $project->setDescription($dto->description);
        }

        if ($dto->links !== null) {
            $project->setLinks($dto->links);
        }

        // Mise à jour des technologies si non null
        if ($technologies !== null) {
            // Vider toutes les technologies actuelles
            foreach ($project->getTechnologies() as $tech) {
                $project->removeTechnology($tech);
            }

            // Ajouter les nouvelles technologies
            foreach ($technologies as $technology) {
                $project->addTechnology($technology);
            }
        }

        return $project;
    }
}