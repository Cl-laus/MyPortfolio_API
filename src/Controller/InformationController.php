<?php
namespace App\Controller;

use App\Entity\Information;
use App\Repository\InformationRepository;
use App\Services\ImageUploadService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

final class InformationController extends AbstractController
{
    public function __construct(
        private InformationRepository $informationRepository,
        private SerializerInterface $serializer,
        private ImageUploadService $uploadService
    ) {}

    #[Route('/api/informations', name: 'api_informations_show', methods: ['GET'])]
    public function show(): JsonResponse
    {
        $information = $this->informationRepository->findAll();
        return $this->json($information, 200, [], ['groups' => 'information:read']);
    }

    #[Route('/api/admin/informations/{id}', name: 'api_admin_informations_update', methods: ['PATCH'], requirements: ['id' => Requirement::DIGITS])]
    public function update(#[MapEntity] Information $information, Request $request): JsonResponse
    {
        try {
            // information:write : groupe dédié à l'écriture — exclut id et photoPath
            $this->serializer->deserialize(
                $request->getContent(),
                Information::class,
                'json',
                [
                    AbstractNormalizer::OBJECT_TO_POPULATE => $information,
                    AbstractNormalizer::GROUPS => ['information:write'],
                ]
            );
            $this->informationRepository->save($information);
        } catch (\DomainException $e) {
            return $this->json(['errors' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return $this->json(['errors' => 'Une erreur inattendue est survenue.'], 500);
        }

        return $this->json($information, 200, [], ['groups' => 'information:read']);
    }

    #[Route('/api/admin/informations/{id}/photo', name: 'api_admin_informations_upload_photo', methods: ['POST'], requirements: ['id' => Requirement::DIGITS])]
    public function uploadPhoto(#[MapEntity] Information $information, Request $request): JsonResponse
    {
        $file = $request->files->get('photo');
        if (!$file) {
            return $this->json(['errors' => 'Aucune photo fournie.'], 400);
        }

        try {
            if ($information->getPhotoPath()) {
                $this->uploadService->delete($information->getPhotoPath());
            }
            $path = $this->uploadService->uploadProfilePhoto($file);
            $information->setPhotoPath($path);
            $this->informationRepository->save($information);
        } catch (\DomainException $e) {
            return $this->json(['errors' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return $this->json(['errors' => 'Erreur lors de l\'upload de la photo.'], 500);
        }

        return $this->json($information, 200, [], ['groups' => 'information:read']);
    }
}
