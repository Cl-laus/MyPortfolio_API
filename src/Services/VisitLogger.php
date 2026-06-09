<?php
namespace App\Services;

use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Command;
use MongoDB\Driver\Manager;
use MongoDB\BSON\UTCDateTime;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

// Enregistre les visites de projets dans MongoDB.
class VisitLogger
{
    private ?Manager $manager = null;

    private string $database   = 'portfolio';
    private string $collection = 'project_visits';

    public function __construct(
        #[Autowire('%env(MONGODB_URL)%')]
        string $mongodbUrl
    ) {
        try {
            $this->manager = new Manager($mongodbUrl);
        } catch (\Exception) {
            // MongoDB indisponible
        }
    }

    // Enregistre une visite pour le projet donné
    public function logVisit(int $projectId): void
    {
        if ($this->manager === null) {
            return;
        }

        try {
            $bulk = new BulkWrite();
            $bulk->insert([
                'project_id' => $projectId,
                'visited_at' => new UTCDateTime(),
            ]);
            $this->manager->executeBulkWrite(
                $this->database . '.' . $this->collection,
                $bulk
            );
        } catch (\Exception) {
            // Silent fail
        }
    }

    // Retourne le nombre de visites groupé par projet, trié par le plus visité
    public function getStatsByProject(): array
    {
        if ($this->manager === null) {
            return [];
        }

        try {
            $command = new Command([
                'aggregate' => $this->collection,
                'pipeline'  => [
                    ['$group' => ['_id' => '$project_id', 'visits' => ['$sum' => 1]]],
                    ['$sort'  => ['visits' => -1]],
                ],
                'cursor' => new \stdClass(),
            ]);

            $cursor = $this->manager->executeCommand($this->database, $command);

            $stats = [];
            foreach ($cursor as $row) {
                $stats[] = [
                    'project_id' => $row->_id,
                    'visits'     => $row->visits,
                ];
            }

            return $stats;
        } catch (\Exception) {
            return [];
        }
    }
}
