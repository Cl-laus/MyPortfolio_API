<?php
namespace App\Tests\Unit\Service;

use App\Services\DisplayOrderManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class DisplayOrderManagerTest extends TestCase
{
    private DisplayOrderManager $manager;

    protected function setUp(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $this->manager = new DisplayOrderManager($em);
    }

    public function testValidateDisplayOrderThrowsWhenOrderIsZero(): void
    {
        $this->expectException(\DomainException::class);

        $this->manager->validateDisplayOrder(0, 5);
    }

    public function testValidateDisplayOrderThrowsWhenOrderExceedsMax(): void
    {
        $this->expectException(\DomainException::class);

        $this->manager->validateDisplayOrder(6, 5);
    }

    public function testValidateDisplayOrderDoesNotThrowForValidOrder(): void
    {
        $this->expectNotToPerformAssertions();

        $this->manager->validateDisplayOrder(3, 5);
    }
}
