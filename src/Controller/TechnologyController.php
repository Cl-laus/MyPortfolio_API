<?php
namespace App\Controller;

use App\Entity\Technology;
use App\Repository\TechnologyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
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
        $data         = $this->serializer->serialize($technologies, 'json');
        return new JsonResponse($data, JsonResponse::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: '_detail', methods: ['GET'])]
    public function showDetail(Technology $technology): JsonResponse
    {
        $data = $this->serializer->serialize($technology, 'json');
        return new JsonResponse($data, JsonResponse::HTTP_OK, [], true);
    }

    #[Route('', name: '_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] Technology $technology): JsonResponse
    {

        return new JsonResponse();
    }
}
