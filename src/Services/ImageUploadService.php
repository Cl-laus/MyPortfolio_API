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

    /**
     * Upload une image pour un projet
     * @param UploadedFile $file
     * @param Project $project
     * @param int $displayOrder
     * @return string URL de l'image
     */
    public function upload(UploadedFile $file, Project $project, int $displayOrder): string
    {
      

        // Nom unique : img_{projectId}_{displayOrder}.{ext}
        $filename = sprintf(
            'img_%d_%d.%s',
            $project->getId(),
            $displayOrder,
            strtolower($file->getClientOriginalExtension())
        );

        // Déplacer le fichier sans conversion
        $file->move($this->uploadDir, $filename);

        return '/uploads/' . $filename;
    }

    /**
     * Upload d'une photo de profil
     */
    public function uploadProfilePhoto(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = 'profile_photo.' . $extension;
        $file->move($this->uploadDir, $filename);
        return '/uploads/' . $filename;
    }

    /**
     * Supprime un fichier depuis son URL
     */
    public function delete(string $url): void
    {
        $filename = basename($url);
        $filepath = $this->uploadDir . '/' . $filename;

        if (is_file($filepath)) {
            unlink($filepath);
        }
    }
}