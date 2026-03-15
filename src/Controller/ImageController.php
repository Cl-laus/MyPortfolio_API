<?php

namespace App\Controller;

use App\Entity\Image;
use App\Repository\ImageRepository;
use App\Services\ImageManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
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

    //////////////////// CREATE /////////////////////////////////
    // se fait dans le ProjectController car les images sont toujours liées à un projet

    //////////////////// UPDATE displayOrder admin /////////////////////////////////
    #[Route('/api/admin/images/{id}', name: 'api_admin_images_update', methods: ['PATCH'], requirements: ['id' => Requirement::DIGITS])]
    public function update(int $id, Request $request): JsonResponse
    {
        // Récupérer le nouveau displayOrder depuis le body
        $data = json_decode($request->getContent(), true);
        if (!isset($data['displayOrder'])) {
            return $this->json(['errors' => 'displayOrder est requis.'], 400);
        }

        try {
            $image = $this->imageManager->updateDisplayOrder($id, $data['displayOrder']);
        } catch (\DomainException $e) {
            return $this->json(['errors' => $e->getMessage()], 400);
        }
        return $this->json($image, 200, [], ['groups' => 'image:read']);
    }
    //////////////////// DELETE admin /////////////////////////////////
    #[Route('/api/admin/images/{id}', name: 'api_admin_images_delete', methods: ['DELETE'], requirements: ['id' => Requirement::DIGITS])]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->imageManager->delete($id);
        } catch (\DomainException $e) {
            return $this->json(['errors' => $e->getMessage()], 400);
        }
        return new JsonResponse(null, 204);
    }
}
