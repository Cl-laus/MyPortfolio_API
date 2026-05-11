<?php

namespace App\Controller;

use App\Repository\ImageRepository;
use App\Services\ImageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

final class ImageController extends AbstractController
{
    public function __construct(
        private ImageRepository $imageRepository,
        private ImageManager $imageManager
    ) {}

    // CREATE : géré dans ProjectController car les images sont toujours liées à un projet

    #[Route('/api/admin/images/{id}', name: 'api_admin_images_update', methods: ['PATCH'], requirements: ['id' => Requirement::DIGITS])]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Vérifie que le JSON est valide et contient displayOrder
        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['displayOrder'])) {
            return $this->json(['errors' => 'displayOrder est requis.'], 400);
        }

        try {
            $image = $this->imageManager->updateDisplayOrder($id, $data['displayOrder']);
        } catch (\DomainException $e) {
            return $this->json(['errors' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return $this->json(['errors' => 'Une erreur inattendue est survenue.'], 500);
        }

        return $this->json($image, 200, [], ['groups' => 'image:read']);
    }

    #[Route('/api/admin/images/{id}', name: 'api_admin_images_delete', methods: ['DELETE'], requirements: ['id' => Requirement::DIGITS])]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->imageManager->delete($id);
        } catch (\DomainException $e) {
            return $this->json(['errors' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return $this->json(['errors' => 'Une erreur inattendue est survenue.'], 500);
        }

        return new JsonResponse(null, 204);
    }
}
