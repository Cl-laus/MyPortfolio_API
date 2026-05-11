<?php
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

final class SocialNetworkController extends AbstractController
{
    public function __construct(
        private SocialNetworkRepository $socialNetworkRepository,
        private SerializerInterface $serializer
    ) {}

    #[Route('/api/social-networks', name: 'api_social_networks_list', methods: ['GET'])]
    public function showList(): JsonResponse
    {
        $socialNetworks = $this->socialNetworkRepository->findAll();
        return $this->json($socialNetworks, 200, [], ['groups' => 'social_network:read']);
    }

    #[Route('/api/social-networks/{id}', name: 'api_social_networks_detail', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    public function showDetail(#[MapEntity] SocialNetwork $socialNetwork): JsonResponse
    {
        return $this->json($socialNetwork, 200, [], ['groups' => 'social_network:read']);
    }

    #[Route('/api/admin/social-networks', name: 'api_admin_social_networks_create', methods: ['POST'])]
    public function create(
        // social_network:write : groupe de désérialisation — exclut id
        #[MapRequestPayload(serializationContext: ['groups' => 'social_network:write'])]
        SocialNetwork $newSocialNetwork
    ): JsonResponse {
        try {
            $this->socialNetworkRepository->save($newSocialNetwork);
        } catch (\Exception $e) {
            return $this->json(['errors' => 'Une erreur inattendue est survenue.'], 500);
        }

        return $this->json($newSocialNetwork, 201, [], ['groups' => 'social_network:read']);
    }

    #[Route('/api/admin/social-networks/{id}', name: 'api_admin_social_networks_update', methods: ['PATCH'], requirements: ['id' => Requirement::DIGITS])]
    public function update(#[MapEntity] SocialNetwork $socialNetwork, Request $request): JsonResponse
    {
        try {
            $this->serializer->deserialize(
                $request->getContent(),
                SocialNetwork::class,
                'json',
                [
                    AbstractNormalizer::OBJECT_TO_POPULATE => $socialNetwork,
                    // social_network:write : groupe dédié à l'écriture — exclut id
                    AbstractNormalizer::GROUPS => ['social_network:write'],
                ]
            );
            $this->socialNetworkRepository->save($socialNetwork);
        } catch (\DomainException $e) {
            return $this->json(['errors' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return $this->json(['errors' => 'Une erreur inattendue est survenue.'], 500);
        }

        return $this->json($socialNetwork, 200, [], ['groups' => 'social_network:read']);
    }

    #[Route('/api/admin/social-networks/{id}', name: 'api_admin_social_networks_delete', methods: ['DELETE'], requirements: ['id' => Requirement::DIGITS])]
    public function delete(#[MapEntity] SocialNetwork $socialNetwork): JsonResponse
    {
        try {
            $this->socialNetworkRepository->delete($socialNetwork);
        } catch (\Exception $e) {
            return $this->json(['errors' => 'Une erreur inattendue est survenue.'], 500);
        }

        return new JsonResponse(null, 204);
    }
}
