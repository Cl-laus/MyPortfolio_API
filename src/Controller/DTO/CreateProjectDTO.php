<?php
namespace App\Controller\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateProjectDTO
{
    #[Assert\NotBlank]
    public string $title;

    #[Assert\NotBlank]
    public string $summary;

    #[Assert\NotBlank]
    public string $description;

    #[Assert\Type('array')]
    public ?array $links = null;

    /** @var int[] */
    #[Assert\NotNull]
    #[Assert\All([new Assert\Type('integer')])]
    //tous les eléments du tableau doivent être des entiers
    public array $technologies = [];
}
