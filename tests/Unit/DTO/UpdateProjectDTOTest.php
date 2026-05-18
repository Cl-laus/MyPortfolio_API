<?php
namespace App\Tests\Unit\DTO;

use App\Controller\DTO\UpdateProjectDTO;
use PHPUnit\Framework\TestCase;

class UpdateProjectDTOTest extends TestCase
{
    public function testAllFieldsAreNullByDefault(): void
    {
        $dto = new UpdateProjectDTO();

        $this->assertNull($dto->displayOrder);
        $this->assertNull($dto->title);
        $this->assertNull($dto->summary);
        $this->assertNull($dto->description);
        $this->assertNull($dto->links);
        $this->assertNull($dto->technologies);
    }
}
