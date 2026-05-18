<?php
namespace App\Tests\Unit\Entity;

use App\Entity\Technology;
use PHPUnit\Framework\TestCase;

class TechnologyTest extends TestCase
{
    public function testIsVisibleReturnsTrueByDefault(): void
    {
        $technology = new Technology();

        $this->assertTrue($technology->isVisible());
    }
}
