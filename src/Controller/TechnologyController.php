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

final class TechnologyController extends AbstractController
{
    public function __construct(
        private TechnologyRepository $technologyRepository,
        private SerializerInterface $serializer
    ) {}

    #[Route('/api/technologies', name: 'api_technologies_list', methods: ['GET'])]
    public function showList(): JsonResponse
    {
        $technologies = $this->technologyRepository->findAll();
        return $this->json($technologies, 200, [], ['groups' => 'technology:read']);
    }

    #[Route('/api/technologies/{id}', name: 'api_technologies_detail', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    public function showDetail(#[MapEntity] Technology $technology): JsonResponse
    {
        return $this->json($technology, 200, [], ['groups' => 'technology:read']);
    }

    #[Route('/api/admin/technologies', name: 'api_admin_technologies_create', methods: ['POST'])]
    public function create(
        // technology:write : groupe dédié à la désérialisation — exclut id
        #[MapRequestPayload(serializationContext: ['groups' => 'technology:write'])]
        Technology $newTechnology
    ): JsonResponse {
        try {
            $this->technologyRepository->save($newTechnology);
        } catch (\Exception $e) {
            return $this->json(['errors' => 'Une erreur inattendue est survenue.'], 500);
        }

        return $this->json($newTechnology, 201, [], ['groups' => 'technology:read']);
    }

    #[Route('/api/admin/technologies/{id}', name: 'api_admin_technologies_update', methods: ['PATCH'], requirements: ['id' => Requirement::DIGITS])]
    public function update(#[MapEntity] Technology $technology, Request $request): JsonResponse
    {
        try {
            $this->serializer->deserialize(
                $request->getContent(),
                Technology::class,
                'json',
                [
                    AbstractNormalizer::OBJECT_TO_POPULATE => $technology,
                    // technology:write : groupe dédié à l'écriture — exclut id
                    AbstractNormalizer::GROUPS => ['technology:write'],
                ]
            );
            $this->technologyRepository->save($technology);
        } catch (\DomainException $e) {
            return $this->json(['errors' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return $this->json(['errors' => 'Une erreur inattendue est survenue.'], 500);
        }

        return $this->json($technology, 200, [], ['groups' => 'technology:read']);
    }

    #[Route('/api/admin/technologies/{id}', name: 'api_admin_technologies_delete', methods: ['DELETE'], requirements: ['id' => Requirement::DIGITS])]
    public function delete(#[MapEntity] Technology $technology): JsonResponse
    {
        try {
            $this->technologyRepository->delete($technology);
        } catch (\Exception $e) {
            return $this->json(['errors' => 'Une erreur inattendue est survenue.'], 500);
        }

        return new JsonResponse(null, 204);
    }
}
