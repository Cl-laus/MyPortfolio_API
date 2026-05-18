<?php
namespace App\Tests\Unit\Service;

use App\Services\DisplayOrderManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

// Test unitaire : vérifie la validation des ordres d'affichage sans base de données.
// On utilise un "mock" (faux EntityManager) pour isoler le service.
class DisplayOrderManagerTest extends TestCase
{
    private DisplayOrderManager $manager;

    protected function setUp(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $this->manager = new DisplayOrderManager($em);
    }

    // Un ordre à 0 est invalide (les ordres commencent à 1).
    public function testValidateDisplayOrderThrowsWhenOrderIsZero(): void
    {
        $this->expectException(\DomainException::class);

        $this->manager->validateDisplayOrder(0, 5);
    }

    // Un ordre supérieur au maximum est invalide.
    public function testValidateDisplayOrderThrowsWhenOrderExceedsMax(): void
    {
        $this->expectException(\DomainException::class);

        $this->manager->validateDisplayOrder(6, 5);
    }

    // Un ordre entre 1 et le max est valide — aucune exception ne doit être levée.
    public function testValidateDisplayOrderDoesNotThrowForValidOrder(): void
    {
        $this->expectNotToPerformAssertions();

        $this->manager->validateDisplayOrder(3, 5);
    }
}
