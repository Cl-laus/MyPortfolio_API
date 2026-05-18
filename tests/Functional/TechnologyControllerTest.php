<?php
namespace App\Tests\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TechnologyControllerTest extends WebTestCase
{
    private function getJwtToken(mixed $client): string
    {
        $client->request('POST', '/api/batcave', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'username' => 'test_admin',
            'password' => 'Test1234!',
        ]));

        $data = json_decode($client->getResponse()->getContent(), true);

        return $data['token'];
    }

    public function testCreateTechnologyReturns201(): void
    {
        $client = static::createClient();
        $token = $this->getJwtToken($client);

        $client->request('POST', '/api/admin/technologies', [], [], [
            'CONTENT_TYPE'  => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ], json_encode([
            'name'     => 'PHPUnit',
            'icon'     => 'phpunit.png',
            'category' => 'Test',
        ]));

        $this->assertResponseStatusCodeSame(201);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('PHPUnit', $data['name']);
    }

    public function testDeleteTechnologyReturns204(): void
    {
        $client = static::createClient();
        $token = $this->getJwtToken($client);

        // Créer une technologie à supprimer
        $client->request('POST', '/api/admin/technologies', [], [], [
            'CONTENT_TYPE'       => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ], json_encode([
            'name'     => 'ToDelete',
            'icon'     => 'delete.png',
            'category' => 'Test',
        ]));

        $created = json_decode($client->getResponse()->getContent(), true);
        $id = $created['id'];

        // Supprimer
        $client->request('DELETE', '/api/admin/technologies/' . $id, [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseStatusCodeSame(204);

        // Vérifier qu'elle n'existe plus en base
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->clear();
        $tech = $em->getRepository(\App\Entity\Technology::class)->find($id);
        $this->assertNull($tech);
    }
}
