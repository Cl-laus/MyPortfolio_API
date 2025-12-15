<?php
namespace App\Services;

use App\Controller\DTO\CreateProjectDTO;
use App\Controller\DTO\Mapper\ProjectMapper;
use App\Controller\DTO\UpdateProjectDTO;
use App\Repository\ProjectRepository;
use App\Entity\Project;


//service pour gérer la logique métier liée aux projets
//recoit un DTO depuis le controller, utilise le mapper pour convertir le DTO en entité Project
//utilise le repository pour persister l'entité
//retourne l'entité au controller
//pas de logique métier complexe pour l'instant, mais cette structure permet de la rajouter facilement à l'avenir
class ProjectsManager
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private ProjectMapper $projectMapper
    ) {}



////////// create //////////


    public function create(CreateProjectDTO $dto): Project
    {
        $project = $this->projectMapper->createFromDto($dto);
        $this->projectRepository->save($project); 
        return $project;
    }


////////// update //////////


    public function update(int $id, UpdateProjectDTO $dto): Project
    {
        $project = $this->projectRepository->find($id);
        if (!$project) {
            throw new \DomainException('Projet introuvable.');
        }

        $this->projectMapper->updateFromDto($dto, $project);
        $this->projectRepository->save($project); 
        return $project;
    }

/////////// delete //////////


    public function delete(int $id): void
    {
        $project = $this->projectRepository->find($id);
        if (!$project) {
            throw new \DomainException('Projet introuvable.');
        }

        $this->projectRepository->delete($project); 
    }
}
