<?php
namespace App\Services;

use App\Entity\Project;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageUploadService
{
    private string $uploadDir;

    public function __construct(string $projectDir)
    {
        // Dossier absolu pour stocker toutes les images des projets
        $this->uploadDir = $projectDir . '/public/uploads/projects';
    }

    /**
     * Upload une image et retourne l'URL relative
     */
    public function upload(UploadedFile $file, Project $project, int $displayOrder): string
    {
        // Créer le dossier du projet s'il n'existe pas
        $projectDir = $this->uploadDir . '/' . $project->getId();
        if (!is_dir($projectDir)) {
            mkdir($projectDir, 0755, true);
        }

        // Générer un nom de fichier unique
        $filename = 'img'. $project->getId() . '_' . $displayOrder . '.webp';
        $filepath = $projectDir . '/' . $filename;

        // Déplacer le fichier uploadé
        $file->move($projectDir, $filename);

        // Retourner l'URL relative (pour le frontend)
        return '/uploads/projects/' . $project->getId() . '/' . $filename;
    }

    /**
     * Supprime une image du disque
     */
    public function delete(string $url): void
    {
        // Chemin absolu basé sur kernel.project_dir
        $filepath = $this->uploadDir . '/' . ltrim(str_replace('/uploads/projects/', '', $url), '/');

        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }
}
