<?php
namespace App\Controller;

use App\Services\VisitLogger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

// Protégé par JWT via le firewall api_admin (security.yaml)
final class StatsController extends AbstractController
{
    public function __construct(
        private VisitLogger $visitLogger
    ) {}

    // Retourne les statistiques de visites par projet depuis MongoDB
    #[Route('/api/admin/stats', name: 'api_admin_stats', methods: ['GET'])]
    public function getStats(): JsonResponse
    {
        $stats = $this->visitLogger->getStatsByProject();

        return $this->json(['stats' => $stats]);
    }
}
