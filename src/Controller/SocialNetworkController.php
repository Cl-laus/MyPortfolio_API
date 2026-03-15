<?php
// src/Controller/SocialNetworkController.php
namespace App\Controller;

use App\Entity\SocialNetwork;
use App\Repository\SocialNetworkRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/social-networks', name: 'api_social_networks_')]
final class SocialNetworkController extends AbstractController
{
    public function __construct(
        private SocialNetworkRepository $socialNetworkRepository,
        private SerializerInterface $serializer
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function showList(): JsonResponse
    {
        $socialNetworks = $this->socialNetworkRepository->findAll();
        $data = $this->serializer->serialize($socialNetworks, 'json', ['groups' => 'social_network:read']);
        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/{id}', name: 'detail', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    public function showDetail(#[MapEntity] SocialNetwork $socialNetwork): JsonResponse
    {
        $data = $this->serializer->serialize($socialNetwork, 'json', ['groups' => 'social_network:read']);
        return new JsonResponse($data, 200, [], true);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload(serializationContext: ['groups' => 'social_network:read'])]
        SocialNetwork $newSocialNetwork
    ): JsonResponse {
        $this->socialNetworkRepository->save($newSocialNetwork);
        $data = $this->serializer->serialize($newSocialNetwork, 'json', ['groups' => 'social_network:read']);
        return new JsonResponse($data, 201, [], true);
    }

    #[Route('/{id}', name: 'update', methods: ['PATCH'], requirements: ['id' => Requirement::DIGITS])]
    public function update(#[MapEntity] SocialNetwork $socialNetwork, Request $request): JsonResponse
    {
        $this->serializer->deserialize(
            $request->getContent(),
            SocialNetwork::class,
            'json',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $socialNetwork,
                AbstractNormalizer::GROUPS => ['social_network:read'],
            ]
        );
        $this->socialNetworkRepository->save($socialNetwork);
        return $this->showDetail($socialNetwork);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => Requirement::DIGITS])]
    public function delete(#[MapEntity] SocialNetwork $socialNetwork): JsonResponse
    {
        $this->socialNetworkRepository->delete($socialNetwork);
        return new JsonResponse(null, 204);
    }
}