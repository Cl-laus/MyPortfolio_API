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

    ///////////////////////////////////// GET public /////////////////////////////////////

    #[Route('/api/informations', name: 'api_informations_show', methods: ['GET'])]
    public function show(): JsonResponse
    {
        $information = $this->informationRepository->findAll();
        $data = $this->serializer->serialize($information, 'json', context: ['groups' => 'information:read']);
        return new JsonResponse($data, 200, [], true);
    }

    ////////////////////// UPDATE admin /////////////////////////////////
    // Mise à jour des champs texte de l'entité Information
    // Le controller deserialise le body JSON et met à jour l'entité existante

    #[Route('/api/admin/informations/{id}', name: 'api_admin_informations_update', methods: ['PATCH'], requirements: ['id' => Requirement::DIGITS])]
    public function update(#[MapEntity] Information $information, Request $request): JsonResponse
    {
        $this->serializer->deserialize(
            $request->getContent(),
            Information::class,
            'json',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $information,
                AbstractNormalizer::GROUPS => ['information:read'],
            ]
        );
        $this->informationRepository->save($information);
        $data = $this->serializer->serialize($information, 'json', context: ['groups' => 'information:read']);
        return new JsonResponse($data, 200, [], true);
    }

    ////////////////////// PHOTO UPLOAD admin /////////////////////////////////
    // Les fichiers uploadés ne peuvent pas être traités via MapRequestPayload (multipart/form-data)
    // Le controller récupère le fichier depuis Request et l'envoie à ImageUploadService
    // L'ancienne photo est supprimée si elle existe avant d'uploader la nouvelle

    #[Route('/api/admin/informations/{id}/photo', name: 'api_admin_informations_upload_photo', methods: ['POST'], requirements: ['id' => Requirement::DIGITS])]
    public function uploadPhoto(#[MapEntity] Information $information, Request $request): JsonResponse
    {
        // Récupérer le fichier uploadé
        $file = $request->files->get('photo');
        if (!$file) {
            return $this->json(['errors' => 'Aucune photo fournie.'], 400);
        }

        try {
            // Supprimer l'ancienne photo si elle existe
            if ($information->getPhotoPath()) {
                $this->uploadService->delete($information->getPhotoPath());
            }

            // Uploader la nouvelle photo et mettre à jour le chemin
            $path = $this->uploadService->uploadProfilePhoto($file);
            $information->setPhotoPath($path);
            $this->informationRepository->save($information);
        } catch (\Exception $e) {
            return $this->json(['errors' => $e->getMessage()], 400);
        }

        $data = $this->serializer->serialize($information, 'json', context: ['groups' => 'information:read']);
        return new JsonResponse($data, 200, [], true);
    }
}