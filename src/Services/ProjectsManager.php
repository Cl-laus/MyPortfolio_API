<?php
namespace App\Services;

use App\Controller\DTO\CreateProjectDTO;
use App\Controller\DTO\Mapper\ProjectMapper;
use App\Controller\DTO\UpdateProjectDTO;
use App\Repository\ProjectRepository;
use App\Entity\Project;

/**
 * Service pour gérer la logique métier liée aux projets
 * Gère la création, mise à jour, suppression et réorganisation des projets
 */
class ProjectsManager
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private ProjectMapper $projectMapper
    ) {}

    ////////// CREATE //////////

    public function create(CreateProjectDTO $dto): Project
    {
        // Récupérer le plus grand displayOrder existant
        $maxOrder = $this->projectRepository->getMaxDisplayOrder();
        
        $project = $this->projectMapper->createFromDto($dto);
        
        // Assigner automatiquement le prochain ordre disponible
        $project->setDisplayOrder($maxOrder + 1);
        
        $this->projectRepository->save($project);
        
        return $project;
    }

    ////////// UPDATE //////////

    public function update(int $id, UpdateProjectDTO $dto): Project
    {
        $project = $this->projectRepository->find($id);
        if (!$project) {
            throw new \DomainException('Projet introuvable.');
        }

        // Si l'ordre change, on réorganise AVANT de mapper
        if ($dto->displayOrder !== null && $dto->displayOrder !== $project->getDisplayOrder()) {
            $this->validateDisplayOrder($dto->displayOrder);
            $this->reorderProjects($project, $dto->displayOrder);
        }

        // Mapper les autres modifications
        $this->projectMapper->updateFromDto($dto, $project);
        $this->projectRepository->save($project);
        
        return $project;
    }

    ////////// DELETE //////////

    public function delete(int $id): void
    {
        $project = $this->projectRepository->find($id);
        if (!$project) {
            throw new \DomainException('Projet introuvable.');
        }

        $deletedOrder = $project->getDisplayOrder();

        // Supprimer le projet
        $this->projectRepository->delete($project);

        // Combler le "trou" : décaler tous les projets après celui supprimé
        $this->fillGapOrderAfterDeletion($deletedOrder);
    }

    ////////// PRIVATE METHODS //////////

    /**
     * Réorganise les projets quand un projet change de position
     */
    private function reorderProjects(Project $project, int $newOrder): void
    {
        $oldOrder = $project->getDisplayOrder();
        $allProjects = $this->projectRepository->findAll();
        
        foreach ($allProjects as $p) {
            // On skip le projet qu'on est en train de déplacer
            if ($p->getId() === $project->getId()) {
                continue;
            }
            
            $currentOrder = $p->getDisplayOrder();
            
            // Cas 1 : Projet déplacé vers le BAS (ex: 1 → 3)
            // Les projets entre 2 et 3 remontent d'une position
            if ($newOrder > $oldOrder 
            && $currentOrder > $oldOrder 
            && $currentOrder <= $newOrder) {
                $p->setDisplayOrder($currentOrder - 1);
                $this->projectRepository->save($p);
            }
            // Cas 2 : Projet déplacé vers le HAUT (ex: 3 → 1)
            // Les projets entre 1 et 2 descendent d'une position
            elseif ($newOrder < $oldOrder 
            && $currentOrder >= $newOrder 
            && $currentOrder < $oldOrder) {
                $p->setDisplayOrder($currentOrder + 1);
                $this->projectRepository->save($p);
            }
        }
        
        // Appliquer le nouvel ordre au projet
        $project->setDisplayOrder($newOrder);
    }

    /**
     * Comble le trou laissé par un projet supprimé
     */
    private function fillGapOrderAfterDeletion(int $deletedOrder): void
    {
        $projectsAfter = $this->projectRepository->findProjectsAfterOrder($deletedOrder);
        
        foreach ($projectsAfter as $project) {
            $project->setDisplayOrder($project->getDisplayOrder() - 1);
            $this->projectRepository->save($project);
        }
    }

    /**
     * Valide que le displayOrder est dans une plage acceptable
     */
    private function validateDisplayOrder(int $order): void
    {
        if ($order < 1) {
            throw new \DomainException('L\'ordre d\'affichage doit être supérieur ou égal à 1.');
        }

        $maxOrder = $this->projectRepository->getMaxDisplayOrder();
        if ($order > $maxOrder) {
            throw new \DomainException("L'ordre d'affichage ne peut pas dépasser {$maxOrder}.");
        }
    }
}