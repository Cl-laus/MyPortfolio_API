<?php
namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

// Test fonctionnel : teste l'API de bout en bout avec une vraie base de données (SQLite en test).
class ProjectControllerTest extends WebTestCase
{
    // GET /api/projects/all doit retourner 200 avec un tableau JSON.
    public function testGetProjectsReturns200(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/projects/all');

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
    }
}
