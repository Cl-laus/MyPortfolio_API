<?php
namespace App\Controller\DTO\Mapper;

use App\Controller\DTO\CreateProjectDTO;
use App\Controller\DTO\UpdateProjectDTO;
use App\Entity\Project;
use App\Repository\TechnologyRepository;

class ProjectMapper
{
    public function __construct(private TechnologyRepository $technologyRepository)
    {}

    public function createFromDto(CreateProjectDTO $dto): Project
    {
        $project = new Project();

        $project
            ->setTitle($dto->title)
            ->setSummary($dto->summary)
            ->setDescription($dto->description)
            ->setLinks($dto->links ?? []);
//methode pour rajouter les technologies liées
        $this->syncTechnologies($project, $dto->technologies);

        return $project;
    }

    public function updateFromDto(UpdateProjectDTO $dto, Project $project): Project
    {
        //on met a jour seulement les champs non nuls
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

        if ($dto->technologies !== null) {
            $this->syncTechnologies($project, $dto->technologies);
        }

        return $project;
    }

    private function syncTechnologies(Project $project, array $technologyIds): void
    {

        foreach ($project->getTechnologies() as $tech) {
            //on vide toutes les technologies actuelles
            $project->removeTechnology($tech);
        }

        if (empty($technologyIds)) {
            return;
        }

        //on récupère les technologies correspondantes aux ids et les mets dans un tableau
        $technologies = $this->technologyRepository->findBy(['id' => $technologyIds]);

        //on vérifie que le nombre de technologies récupérées correspond au nombre d'ids fournis
        //si non, il y a eu une erreur (id invalide)
        if (count($technologies) !== count($technologyIds)) {
            throw new \DomainException('Une ou plusieurs technologies sont invalides.');
        }
        //on ajoute les technologies à l'entité Project
        foreach ($technologies as $tech) {
            $project->addTechnology($tech);
        }
    }
}
