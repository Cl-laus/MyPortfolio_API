<?php
namespace App\Controller;

use App\Controller\DTO\CreateProjectDTO;
use App\Controller\DTO\UpdateProjectDTO;
use App\Entity\Project;
use App\Repository\ProjectRepository;

use App\Services\ProjectsManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;


#[Route('/api/projects', name: 'api_projects_')]
final class ProjectController extends AbstractController
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private ValidatorInterface $validator,
        private ProjectsManager $projectManager
    ) {}

    ///////////////////////////////////// GETs /////////////////////////////////////
    // Pas besoin de DTO pour les GETs car on ne modifie pas les données
    // On utilise les groupes de sérialisation pour contrôler les données renvoyées

    #[Route('', name: '_list', methods: ['GET'])]
    public function showList(): JsonResponse
    {
        $projects = $this->projectRepository->findTop3Projects();
        return $this->json($projects, 200, [], ['groups' => 'project:list']);
    }

    #[Route('/{id}', name: '_detail', methods: ['GET'])]
    public function showDetail(#[MapEntity] Project $project): JsonResponse
    {
        return $this->json($project, 200, [], ['groups' => 'project:read']);
    }

    ////////////////////// CREATE, UPDATE, DELETE /////////////////////////////////
    // On utilise des DTO pour les opérations de création et de mise à jour
    // Le controller vérifie le DTO 
    // Il l'envoie au ProjectManager ; qui lui-même appelle le mapper et renvoie l'entité au controller
    // Le controller sérialise l'entité renvoyée par le service et la retourne en réponse

    #[Route('', methods: ['POST'])]
    public function create(#[MapRequestPayload] CreateProjectDTO $dto): JsonResponse
    {
        // Validation des données reçues
        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string)$errors], 400);
        }

        // Appel du manager pour créer le projet
        try {
            $project = $this->projectManager->create($dto);
        } catch (\DomainException $e) {
            // On attrape les exceptions de domaine lancées par le service ou le mapper
            return $this->json(['errors' => $e->getMessage()], 400);
        }

        // Retour du projet créé, sérialisé avec le groupe de lecture
        return $this->json($project, 201, [], ['groups' => 'project:read']);
    }

    #[Route('/{id}', methods: ['PATCH'])]
    public function update(int $id, #[MapRequestPayload] UpdateProjectDTO $dto): JsonResponse
    {
        // Validation des données reçues
        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string)$errors], 400);
        }

        // Appel du manager pour mettre à jour le projet
        try {
            $project = $this->projectManager->update($id, $dto);
        } catch (\DomainException $e) {
            return $this->json(['errors' => $e->getMessage()], 400);
        }

        // Retour du projet mis à jour
        return $this->json($project, 200, [], ['groups' => 'project:read']);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        // Appel du manager pour supprimer le projet et avoir l'erreur si elle existe
        try {
            $this->projectManager->delete($id);
        } catch (\DomainException $e) {
            return $this->json(['errors' => $e->getMessage()], 400);
        }

        // Retour sans contenu
        return new JsonResponse(null, 204);
    }
}
