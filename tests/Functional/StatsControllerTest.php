<?php
namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

// Test fonctionnel : vérifie la sécurité et le bon fonctionnement de l'endpoint stats MongoDB.
class StatsControllerTest extends WebTestCase
{
    // GET /api/admin/stats sans JWT doit retourner 401 (firewall api_admin actif).
    public function testGetStatsWithoutJwtReturns401(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/admin/stats');

        $this->assertResponseStatusCodeSame(401);
    }
}
