<?php

namespace App\Controller;

use App\Entity\Technology;
use App\Repository\TechnologyRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/technologies', name: 'api_technologies_')]
final class TechnologyController extends AbstractController
{
    public function __construct(
        private TechnologyRepository $technologyRepository,
        private SerializerInterface $serializer
    ) {
    }

    #[Route('', name: '_list', methods: ['GET'])]
    public function showList(): JsonResponse
    {
        $technologies = $this->technologyRepository->findAll();
        $data = $this->serializer->serialize(
            $technologies,
            'json',
            context: ['groups' => 'technology:read']
        );

        return new JsonResponse($data, JsonResponse::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: '_detail', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    public function showDetail(
        #[MapEntity] Technology $technology
    ): JsonResponse {
        $data = $this->serializer->serialize(
            $technology,
            'json',
            context: ['groups' => 'technology:read']
        );

        return new JsonResponse($data, JsonResponse::HTTP_OK, [], true);
    }

    #[Route('', name: '_create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload(serializationContext: ['groups' => 'technology:write'])]
        Technology $newTechnology
    ): JsonResponse {
        $this->technologyRepository->save($newTechnology);

        $data = $this->serializer->serialize(
            $newTechnology,
            'json',
            context: ['groups' => 'technology:read']
        );

        return new JsonResponse($data, JsonResponse::HTTP_CREATED, [], true);
    }

    #[Route('/{id}', name: '_update', methods: ['PATCH'],requirements: ['id' => Requirement::DIGITS])]
    public function update(
        #[MapEntity] Technology $technology,
        Request $request
    ): JsonResponse {
        $this->serializer->deserialize(
            $request->getContent(),
            Technology::class,
            'json',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $technology,
                AbstractNormalizer::GROUPS => ['technology:write'],
            ]
            
        );

        $this->technologyRepository->save($technology);

        return $this->showDetail($technology);
    }

    #[Route('/{id}', name: '_delete', methods: ['DELETE'], requirements: ['id' => Requirement::DIGITS])]
    public function delete(
        #[MapEntity] Technology $technology
    ): JsonResponse {
        $this->technologyRepository->delete($technology);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
