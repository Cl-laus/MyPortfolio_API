<?php
namespace App\Controller;

use App\Controller\DTO\CreateProjectDTO;
use App\Controller\DTO\UpdateProjectDTO;
use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Services\ImageManager;
use App\Services\ProjectManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

final class ProjectController extends AbstractController
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private ProjectManager $projectManager,
        private ImageManager $imageManager
    ) {}

    ///////////////////////////////////// GETs publics /////////////////////////////////////

    #[Route('/api/projects', name: 'api_projects_list', methods: ['GET'])]
    public function showList(): JsonResponse
    {
        $projects = $this->projectRepository->findTop3Projects();
        // 'project:list' : groupe de sérialisation qui expose uniquement les champs publics.
        // Les champs non déclarés dans ce groupe (ex: données internes) ne sont jamais retournés.
        return $this->json($projects, 200, [], ['groups' => 'project:list']);
    }

    #[Route('/api/projects/all', name: 'api_projects_all', methods: ['GET'])]
    public function showListAll(): JsonResponse
    {
        $projects = $this->projectRepository->findAll();
        return $this->json($projects, 200, [], ['groups' => 'project:list']);
    }

    // Requirement::DIGITS : l'ID doit être un entier positif.
    // Toute valeur non numérique (chaîne, chemin, injection) retourne automatiquement 404.
    #[Route('/api/projects/{id}', name: 'api_projects_detail', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    public function showDetail(#[MapEntity] Project $project): JsonResponse
    {
        return $this->json($project, 200, [], ['groups' => 'project:read']);
    }

    ////////////////////// CREATE, UPDATE, DELETE admin /////////////////////////////////
    // MapRequestPayload désérialise le body JSON vers un DTO (Data Transfer Object).
    // Seuls les champs déclarés dans le DTO peuvent être modifiés — impossible d'injecter
    // d'autres champs (id, createdAt…) même si on les envoie dans la requête.

    #[Route('/api/admin/projects', name: 'api_admin_projects_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] CreateProjectDTO $dto): JsonResponse
    {
        try {
            $project = $this->projectManager->create($dto);
        } catch (\DomainException $e) {
            return $this->json(['errors' => $e->getMessage()], 400);
        }
        return $this->json($project, 201, [], ['groups' => 'project:read']);
    }

    #[Route('/api/admin/projects/{id}', name: 'api_admin_projects_update', methods: ['PATCH'], requirements: ['id' => Requirement::DIGITS])]
    public function update(int $id, #[MapRequestPayload] UpdateProjectDTO $dto): JsonResponse
    {
        // Appel du manager pour mettre à jour le projet
        try {
            $project = $this->projectManager->update($id, $dto);
        } catch (\DomainException $e) {
            return $this->json(['errors' => $e->getMessage()], 400);
        }

        // Retour du projet mis à jour
        return $this->json($project, 200, [], ['groups' => 'project:read']);
    }

    #[Route('/api/admin/projects/{id}', name: 'api_admin_projects_delete', methods: ['DELETE'], requirements: ['id' => Requirement::DIGITS])]
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

    ////////////////////// IMAGE MANAGEMENT admin /////////////////////////////////
    // Les fichiers uploadés ne peuvent pas être traités via MapRequestPayload (multipart/form-data)
    // Le controller récupère les fichiers depuis Request et les envoie au ImageManager
    // Le manager fait toutes les validations et la logique métier

    #[Route('/api/admin/projects/{id}/images', name: 'api_admin_projects_add_images', methods: ['POST'], requirements: ['id' => Requirement::DIGITS])]
    public function addImages(int $id, Request $request): JsonResponse
    {
        $files = $request->files->get('files') ?? [];
        // Appel du manager pour créer les images
        try {
            $createdImages = $this->imageManager->createMultiple($id, $files);
        } catch (\DomainException $e) {
            return $this->json(['errors' => $e->getMessage()], 400);
        }

        // Retour JSON avec les images créées
        return $this->json($createdImages, 201, [], ['groups' => 'image:read']);
    }
    ////////////////////// TECHNOLOGY ASSOCIATION admin /////////////////////////////////

#[Route('/api/admin/projects/{projectId}/technologies/{technoId}', name: 'api_admin_projects_add_technology', methods: ['POST'], requirements: ['projectId' => Requirement::DIGITS, 'technoId' => Requirement::DIGITS])]
public function addTechnology(int $projectId, int $technoId): JsonResponse
{
    try {
        $project = $this->projectManager->addTechnology($projectId, $technoId);
    } catch (\DomainException $e) {
        return $this->json(['errors' => $e->getMessage()], 400);
    }
    return $this->json($project, 200, [], ['groups' => 'project:read']);
}

#[Route('/api/admin/projects/{projectId}/technologies/{technoId}', name: 'api_admin_projects_remove_technology', methods: ['DELETE'], requirements: ['projectId' => Requirement::DIGITS, 'technoId' => Requirement::DIGITS])]
public function removeTechnology(int $projectId, int $technoId): JsonResponse
{
    try {
        $project = $this->projectManager->removeTechnology($projectId, $technoId);
    } catch (\DomainException $e) {
        return $this->json(['errors' => $e->getMessage()], 400);
    }
    return $this->json($project, 200, [], ['groups' => 'project:read']);
}
}