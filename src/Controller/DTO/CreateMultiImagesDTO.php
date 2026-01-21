<?php
namespace App\Controller\DTO;

use Symfony\Component\Validator\Constraints as Assert;

namespace App\Controller\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateMultiImagesDTO
{
    /**
     * @var CreateImageDTO[]
     */
    #[Assert\NotNull]
    #[Assert\Count(
        min: 1,
        max: 10,
        minMessage: 'Au moins une image est requise',
        maxMessage: 'Maximum 10 images par projet'
    )]
    #[Assert\Valid]
    public array $images = [];
}
