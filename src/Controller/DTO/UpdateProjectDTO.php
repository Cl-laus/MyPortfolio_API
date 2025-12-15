<?php
namespace App\Controller\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateProjectDTO
{
    public ?int $displayOrder = null;
    public ?string $title = null;
    public ?string $summary = null;
    public ?string $description = null;
    public ?array $links = null;

    /** @var int[]|null */
    #[Assert\All([new Assert\Type('integer')])]
    public ?array $technologies = null;
}
