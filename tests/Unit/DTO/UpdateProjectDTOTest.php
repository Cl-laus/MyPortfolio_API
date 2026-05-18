<?php
namespace App\Tests\Unit\DTO;

use App\Controller\DTO\UpdateProjectDTO;
use PHPUnit\Framework\TestCase;

// Test unitaire : vérifie que le DTO de mise à jour projet a tous ses champs à null par défaut.
// Garantit qu'un PATCH partiel (ex: juste le titre) ne modifie pas les autres champs.
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
