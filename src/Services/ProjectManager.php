<?php
namespace App\Services;

use App\Controller\DTO\CreateProjectDTO;
use App\Controller\DTO\Mapper\ProjectMapper;
use App\Controller\DTO\UpdateProjectDTO;
use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Repository\TechnologyRepository;

class ProjectManager
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private TechnologyRepository $technologyRepository,
        private ProjectMapper $projectMapper,
        private DisplayOrderManager $displayOrderManager
    ) {}

    ////////// CREATE //////////

    public function create(CreateProjectDTO $dto): Project
    {
        $technologies = $this->validateAndGetTechnologies($dto->technologies);

        $maxOrder = $this->projectRepository->getMaxDisplayOrder();

        $project = $this->projectMapper->createFromDto($dto, $technologies);

        $project->setDisplayOrder($maxOrder + 1);

        $this->projectRepository->save($project);

        return $project;
    }

    ////////// UPDATE //////////

    public function update(int $id, UpdateProjectDTO $dto): Project
    {
        // 1. Récupérer le projet
        $project = $this->projectRepository->find($id);
        if (!$project) {
            throw new \DomainException('Projet introuvable.');
        }

        // 2. Gestion du displayOrder
        $dtoOrder = $dto->displayOrder;
        $actualOrder = $project->getDisplayOrder();
        $maxOrder = $this->projectRepository->getMaxDisplayOrder();

        if ($dtoOrder !== null && $dtoOrder !== $actualOrder) {
            // Validation
            $this->displayOrderManager->validateDisplayOrder($dtoOrder, $maxOrder);

            // Réorganisation des autres projets
            $this->displayOrderManager->reOrder(
                $this->projectRepository->findAll(),
                $actualOrder,
                $dtoOrder
            );

            // 🔥 IMPORTANT : appliquer le nouvel ordre au projet courant
            $project->setDisplayOrder($dtoOrder);
        }

        // 3. Technologies
        $technologies = null;
        if ($dto->technologies !== null) {
            $technologies = $this->validateAndGetTechnologies($dto->technologies);
        }

        // 4. Mise à jour via mapper
        $this->projectMapper->updateFromDto($dto, $project, $technologies);

        // 5. Save
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

        $this->projectRepository->delete($project);

        $this->displayOrderManager->fillGapAfterDeletion(
            $this->projectRepository->findAll(),
            $deletedOrder
        );
    }

    ////////// TECHNOLOGY //////////

    public function addTechnology(int $projectId, int $technoId): Project
    {
        $project = $this->projectRepository->find($projectId);
        if (!$project) {
            throw new \DomainException('Projet introuvable.');
        }

        $technology = $this->technologyRepository->find($technoId);
        if (!$technology) {
            throw new \DomainException('Technologie introuvable.');
        }

        $project->addTechnology($technology);
        $this->projectRepository->save($project);

        return $project;
    }

    public function removeTechnology(int $projectId, int $technoId): Project
    {
        $project = $this->projectRepository->find($projectId);
        if (!$project) {
            throw new \DomainException('Projet introuvable.');
        }

        $technology = $this->technologyRepository->find($technoId);
        if (!$technology) {
            throw new \DomainException('Technologie introuvable.');
        }

        $project->removeTechnology($technology);
        $this->projectRepository->save($project);

        return $project;
    }

    ////////// PRIVATE //////////

    private function validateAndGetTechnologies(array $technologyIds): array
    {
        if (empty($technologyIds)) {
            return [];
        }

        $technologies = $this->technologyRepository->findBy(['id' => $technologyIds]);

        if (count($technologies) !== count($technologyIds)) {
            throw new \DomainException('Une ou plusieurs technologies sont invalides.');
        }

        return $technologies;
    }
}