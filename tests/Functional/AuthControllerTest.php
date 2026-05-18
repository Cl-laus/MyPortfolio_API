<?php
namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthControllerTest extends WebTestCase
{
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
