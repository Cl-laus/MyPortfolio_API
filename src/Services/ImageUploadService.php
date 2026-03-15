<?php

namespace App\Services;

use App\Entity\Project;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ImageUploadService
{
    private string $uploadDir;

    public function __construct(ParameterBagInterface $params)
    {
        $projectDir = $params->get('kernel.project_dir');
        $this->uploadDir = $projectDir . '/public/uploads';
    }

 
    public function upload(UploadedFile $file, Project $project, int $displayOrder): string
    {
        // Nom unique Ex: img_1_3
        $filename = sprintf(
            'img_%d_%d.webp',
            $project->getId(),
            $displayOrder
        );
        $file->move($this->uploadDir, $filename);
        return '/uploads/' . $filename;
    }


    public function delete(string $url): void
    {
        $filename = basename($url);
        $filepath = $this->uploadDir . '/' . $filename;

        if (is_file($filepath)) {
            unlink($filepath);
        }
    }
}