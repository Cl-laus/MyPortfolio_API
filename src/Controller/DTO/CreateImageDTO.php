<?php
namespace App\Controller\DTO;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class CreateImageDTO
{
    #[Assert\NotNull]
    #[Assert\File(
        maxSize: '5M',
        mimeTypes: ['image/webp'],
        mimeTypesMessage: 'Le fichier doit être au format WebP'
    )]
    public UploadedFile $file;
}
