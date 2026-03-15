<?php
// src/Controller/InformationController.php
namespace App\Controller;

use App\Entity\Information;
use App\Repository\InformationRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/informations', name: 'api_informations_')]
final class InformationController extends AbstractController
{
    public function __construct(
        private InformationRepository $informationRepository,
        private SerializerInterface $serializer
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function show(): JsonResponse
    {
        $information = $this->informationRepository->findAll();
        $data = $this->serializer->serialize($information, 'json', context: ['groups' => 'information:read']);
        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/{id}', name: 'update', methods: ['PATCH'], requirements: ['id' => Requirement::DIGITS])]
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
}