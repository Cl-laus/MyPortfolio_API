<?php
namespace App\Services;

use App\Controller\DTO\CreateProjectDTO;
use App\Controller\DTO\Mapper\ProjectMapper;
use App\Controller\DTO\UpdateProjectDTO;
use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Repository\TechnologyRepository;

/**
 * Service pour gérer la logique métier liée aux projets
 * Gère la création, mise à jour, suppression et réorganisation des projets en appelant d'autres services
 */
class ProjectsManager
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
        // 1. Valider et récupérer les technologies
        $technologies = $this->validateAndGetTechnologies($dto->technologies);

        // 2. Récupérer le plus grand displayOrder existant en BDD
        $maxOrder = $this->projectRepository->getMaxDisplayOrder();

        // 3. Transformer le DTO en entité via le mapper
        $project = $this->projectMapper->createFromDto($dto, $technologies);

        // 4. Assigner automatiquement le prochain ordre disponible
        $project->setDisplayOrder($maxOrder + 1);

        // 5. Persister le projet
        $this->projectRepository->save($project);

        return $project;
    }

    ////////// UPDATE //////////

    public function update(int $id, UpdateProjectDTO $dto): Project
    {
        //1. Récupérer le projet existant ou erreur
        $project = $this->projectRepository->find($id);
        if (! $project) {
            throw new \DomainException('Projet introuvable.');
        }

        //2. Si l'ordre change, on réorganise les autres projets
        $dtoOrder = $dto->displayOrder;
        $actualOrder = $project->getDisplayOrder();
        $maxOrder = $this->projectRepository->getMaxDisplayOrder();

        if ($dtoOrder== null && $dtoOrder !== $actualOrder) {
            $this->displayOrderManager->validateDisplayOrder($dtoOrder, $maxOrder);

            $this->displayOrderManager->reOrder(
                $this->projectRepository->findAll(),
                $actualOrder,
                $dtoOrder
            );
        }

        //3. Valider et récupérer les technologies si elles existent dans le DTO
        $technologies = null;
        if ($dto->technologies !== null) {
            $technologies = $this->validateAndGetTechnologies($dto->technologies);
        }

        // 4. Transformer le DTO en entité via le mapper
        $this->projectMapper->updateFromDto($dto, $project, $technologies);
        // 5. Persister le projet
        $this->projectRepository->save($project);

        return $project;
    }

    ////////// DELETE //////////

    public function delete(int $id): void
    {
        //1. Récupérer le projet existant ou erreur
        $project = $this->projectRepository->find($id);
        if (! $project) {
            throw new \DomainException('Projet introuvable.');
        }
        //2. Récupérer l'ordre du projet avant suppression
        $deletedOrder = $project->getDisplayOrder();

        //3.Supprimer le projet
        $this->projectRepository->delete($project);

        //4.Combler le "trou" : décaler tous les projets après celui supprimé
        $this->displayOrderManager->fillGapAfterDeletion(
            $this->projectRepository->findAll(),
            $deletedOrder
        );
    }

    
    ///////////////// PRIVATE METHODS //////////

    /**
     * Valide et récupère les entités Technology à partir d'un tableau d'IDs
     *
     * @param int[] recoit des IDs de technologies
     * @return Technology[] renvoit des entités Technology
     * @throws \DomainException Si une ou plusieurs technologies sont invalides
     */
    private function validateAndGetTechnologies(array $technologyIds): array
    {
        // Cas particulier : tableau vide = aucune technologie
        if (empty($technologyIds)) {return [];}

        // Récupérer les technologies correspondantes aux IDs
        $technologies = $this->technologyRepository->findBy(['id' => $technologyIds]);

        // Vérifier que toutes les technologies existent
        if (count($technologies) !== count($technologyIds)) {
            throw new \DomainException('Une ou plusieurs technologies sont invalides.');
        }
        return $technologies;
    }
}
