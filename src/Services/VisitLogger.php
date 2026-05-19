<?php
namespace App\Services;

use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\BSON\UTCDateTime;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Enregistre les visites de projets dans MongoDB.
 * Choix NoSQL justifié : événements append-only, pas de jointures, schéma libre.
 * Fail-safe : si MongoDB est indisponible, les visites ne sont pas loggées
 * mais l'API continue de fonctionner normalement.
 */
class VisitLogger
{
    private ?Collection $collection = null;

    public function __construct(
        #[Autowire('%env(MONGODB_URL)%')]
        string $mongodbUrl
    ) {
        try {
            $client = new Client($mongodbUrl);
            // Base : "portfolio", collection : "project_visits"
            $this->collection = $client->portfolio->project_visits;
        } catch (\Exception $e) {
            // MongoDB indisponible — on continue sans logger
        }
    }

    // Enregistre une visite pour le projet donné
    public function logVisit(int $projectId): void
    {
        if ($this->collection === null) {
            return;
        }

        try {
            $this->collection->insertOne([
                'project_id' => $projectId,
                'visited_at' => new UTCDateTime(),
            ]);
        } catch (\Exception $e) {
            // Silent fail — ne pas casser la réponse API si MongoDB plante
        }
    }

    // Retourne le nombre de visites groupé par projet, trié par le plus visité
    public function getStatsByProject(): array
    {
        if ($this->collection === null) {
            return [];
        }

        try {
            $cursor = $this->collection->aggregate([
                ['$group' => ['_id' => '$project_id', 'visits' => ['$sum' => 1]]],
                ['$sort'  => ['visits' => -1]],
            ]);

            $stats = [];
            foreach ($cursor as $row) {
                $stats[] = [
                    'project_id' => $row['_id'],
                    'visits'     => $row['visits'],
                ];
            }

            return $stats;
        } catch (\Exception $e) {
            return [];
        }
    }
}
