<?php
namespace App\Tests\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

// Test fonctionnel : vérifie les opérations CRUD sur les technologies via l'API.
// Ces tests nécessitent un token JWT admin — on le récupère en appelant /api/batcave.
class TechnologyControllerTest extends WebTestCase
{
    // Connexion en tant qu'admin pour obtenir un JWT valide.
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

    // Créer une technologie en tant qu'admin doit retourner 201 avec l'objet créé.
    public function testCreateTechnologyReturns201(): void
    {
        $client = static::createClient();
        $token = $this->getJwtToken($client);

        $client->request('POST', '/api/admin/technologies', [], [], [
            'CONTENT_TYPE'       => 'application/json',
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

    // Supprimer une technologie doit retourner 204 et la retirer de la base.
    public function testDeleteTechnologyReturns204(): void
    {
        $client = static::createClient();
        $token = $this->getJwtToken($client);

        // On crée d'abord une technologie pour pouvoir la supprimer.
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

        // Suppression.
        $client->request('DELETE', '/api/admin/technologies/' . $id, [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseStatusCodeSame(204);

        // Vérification en base : la technologie ne doit plus exister.
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->clear();
        $tech = $em->getRepository(\App\Entity\Technology::class)->find($id);
        $this->assertNull($tech);
    }
}
