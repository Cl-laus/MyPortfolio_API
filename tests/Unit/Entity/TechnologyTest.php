<?php
namespace App\Tests\Unit\Entity;

use App\Entity\Technology;
use PHPUnit\Framework\TestCase;

// Test unitaire : vérifie le comportement de l'entité Technology sans base de données.
class TechnologyTest extends TestCase
{
    // Une technologie doit être visible par défaut à sa création.
    public function testIsVisibleReturnsTrueByDefault(): void
    {
        $technology = new Technology();

        $this->assertTrue($technology->isVisible());
    }
}
