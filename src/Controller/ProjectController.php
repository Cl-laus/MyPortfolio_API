<?php
namespace App\Controller;

use App\Entity\Project;
use App\Repository\ProjectRepository;
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

#[Route('/api/projects', name: 'api_projects_')]
final class ProjectController extends AbstractController
{

    public function __construct(
        private ProjectRepository $projectRepository,
        private SerializerInterface $serializer,
        private TechnologyRepository $technologyRepository,
    ) {}
//GETS METHODS
    #[Route('', name: '_list', methods: ['GET'])]
    public function showList(): JsonResponse
    {
        $projects = $this->projectRepository->findTop3Projects();
        $data     = $this->serializer->serialize(
            $projects,
            'json',
            context: ['groups' => 'project:list']
        );

        return new JsonResponse($data, JsonResponse::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: '_detail', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    public function showDetail(
        #[MapEntity] Project $project
    ): JsonResponse {

        $data = $this->serializer->serialize(
            $project,
            'json',
            context: ['groups' => 'project:read']
        );

        return new JsonResponse($data, JsonResponse::HTTP_OK, [], true);
    }
// CREATE METHOD
    #[Route('', name: '_create', methods: ['POST'])]
    public function create(
        Request $request,
        #[MapRequestPayload(serializationContext: ['groups' => 'project:write'])]
        Project $newProject
        //on mappe les données de la requête vers une nouvelle entité Project
    ): JsonResponse {
        //soucis pour les relations donc on recupere le body
        $requestData = $request->toArray();

        //on ajoute les technologies liées aux id envoyés dans la requête
        if (! empty($requestData['technologies'])) {
            foreach ($requestData['technologies'] as $techId) {
                $tech = $this->technologyRepository->find($techId);
                if ($tech) {
                    $newProject->addTechnology($tech);
                }
            }
        }
//TODO gerer les images et liens plus tard


        $this->projectRepository->save($newProject);
        //on save le nouveau projet puis on le sérialise pour le renvoyer en réponse
        $data = $this->serializer->serialize(
            $newProject,
            'json',
            context: ['groups' => 'project:read']
        );
        return new JsonResponse($data, JsonResponse::HTTP_CREATED, [], true);
    }
    // UPDATE METHOD
    #[Route('/{id}', name: '_update', methods: ['PATCH'], requirements: ['id' => Requirement::DIGITS])]
    public function update(
        #[MapEntity] Project $project,
        Request $request
    ): JsonResponse {
        $this->serializer->deserialize(
            $request->getContent(),
            Project::class,
            'json',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $project,
                AbstractNormalizer::GROUPS             => ['project:write'],
            ]
            // on dezerialize les données contenues dans la requête,
            //on met à jour l'entité Technology existante

        );
        $this->projectRepository->save($project);
        return $this->showDetail($project);

    }

//DELETE METHOD
    #[Route('/{id}', name: '_delete', methods: ['DELETE'], requirements: ['id' => Requirement::DIGITS])]
    public function delete(
        #[MapEntity] Project $project
    ): JsonResponse {
        $this->projectRepository->delete($project);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

}
