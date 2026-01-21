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

#[Route('/api/projects', name: 'api_projects_')]
final class ProjectController extends AbstractController
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private ProjectManager $projectManager,
        private ImageManager $imageManager
    ) {}

    ///////////////////////////////////// GETs /////////////////////////////////////
    // Pas besoin de DTO pour les GETs car on ne modifie pas les données
    // On utilise les groupes de sérialisation pour contrôler les données renvoyées

    #[Route('', name: 'list', methods: ['GET'])]
    public function showList(): JsonResponse
    {
        $projects = $this->projectRepository->findTop3Projects();
        return $this->json($projects, 200, [], ['groups' => 'project:list']);
    }

    #[Route('/all', name: '_all', methods: ['GET'])]
    public function showListAll(): JsonResponse
    {
        $projects = $this->projectRepository->findAll();
        return $this->json($projects, 200, [], ['groups' => 'project:list']);
    }

    #[Route('/{id}', name: 'detail', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    public function showDetail(#[MapEntity] Project $project): JsonResponse
    {
        return $this->json($project, 200, [], ['groups' => 'project:read']);
    }

    ////////////////////// CREATE, UPDATE, DELETE /////////////////////////////////
    // On utilise des DTO pour les opérations de création et de mise à jour
    // Le controller vérifie le DTO via MapRequestPayload
    // Il l'envoie au ProjectManager ; qui lui-même appelle le mapper et renvoie l'entité au controller
    // Le controller sérialise l'entité renvoyée par le service et la retourne en réponse

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(#[MapRequestPayload] CreateProjectDTO $dto): JsonResponse
    {
        try {
            $project = $this->projectManager->create($dto);
        } catch (\DomainException $e) {
            return $this->json(['errors' => $e->getMessage()], 400);
        }
        return $this->json($project, 201, [], ['groups' => 'project:read']);
    }

    #[Route('/{id}', name: 'update', methods: ['PATCH'], requirements: ['id' => Requirement::DIGITS])]
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

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => Requirement::DIGITS])]
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

    ////////////////////// IMAGE MANAGEMENT /////////////////////////////////
    // Les fichiers uploadés ne peuvent pas être traités via MapRequestPayload (multipart/form-data)
    // Le controller récupère les fichiers depuis Request et les envoie au ImageManager
    // Le manager fait toutes les validations et la logique métier

    #[Route('/{id}/images', name: 'add_images', methods: ['POST'], requirements: ['id' => Requirement::DIGITS])]
    public function addImages(int $id, Request $request): JsonResponse
    {
        // Récupérer les fichiers uploadés
        $files = $request->files->all();
        // Appel du manager pour créer les images
        try {
            $createdImages = $this->imageManager->createMultiple($id, $files);
        } catch (\DomainException $e) {
            return $this->json(['errors' => $e->getMessage()], 400);
        }

        // Retour JSON avec les images créées
        return $this->json($createdImages, 201, [], ['groups' => 'image:read']);
    }
}