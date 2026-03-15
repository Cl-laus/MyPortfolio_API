<?php
namespace App\Controller;

use App\Entity\Image;
use App\Repository\ImageRepository;
use App\Services\ImageManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

final class ImageController extends AbstractController
{
    public function __construct(
        private ImageRepository $imageRepository,
        private ImageManager $imageManager
    ) {}

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