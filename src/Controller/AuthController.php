<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class AuthController extends AbstractController
{
    #[Route('/api/batcave', name: 'api_batcave', methods: ['POST'])]
    public function login(): JsonResponse
    {
        // Le firewall json_login intercepte la requête avant d'arriver ici
        // ce contrôleur ne sera jamais exécuté, mais il doit exister pour que le firewall puisse rediriger vers lui
        throw new \LogicException('Ce controller ne doit pas être appelé directement.');
    }
}