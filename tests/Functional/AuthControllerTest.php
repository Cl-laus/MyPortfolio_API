<?php
namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

// Test fonctionnel : vérifie l'authentification JWT.
class AuthControllerTest extends WebTestCase
{
    // Un login avec de mauvais credentials doit retourner 401 (non autorisé).
    public function testLoginWithWrongCredentialsReturns401(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/batcave', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'username' => 'wrong_user',
            'password' => 'wrong_password',
        ]));

        $this->assertResponseStatusCodeSame(401);
    }
}
