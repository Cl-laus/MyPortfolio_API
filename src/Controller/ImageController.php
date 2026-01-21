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

#[Route('api/images', name: 'api_images_')]
final class ImageController extends AbstractController
{
    public function __construct(
        private ImageRepository $imageRepository,
        private ImageManager $imageManager
    ) {}
//////////////////////////////////////// GETs /////////////////////////////////////
//pas utiles.....
    #[Route('', name: '_list', methods: ['GET'])]
    public function showList(): JsonResponse
    {
        $images = $this->imageRepository->findAll();
        return $this->json($images, 200, [], ['groups' => 'image:read']);
    }

    #[Route('/{id}', name: '_detail', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    public function showDetail(#[MapEntity] Image $image): JsonResponse
    {
        return $this->json($image, 200, [], ['groups' => 'image:read']);
    }



////////////////////// CREATE, UPDATE////////////
// se fera dans le project controller

//////////////////// DELETE /////////////////////////////////
    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => Requirement::DIGITS])]
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
