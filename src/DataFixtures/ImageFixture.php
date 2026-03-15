<?php
namespace App\DataFixtures;

use App\Entity\Image;
use App\Entity\Project;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ImageFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Récupérer tous les projets
        $projects = $manager->getRepository(Project::class)->findAll();

        foreach ($projects as $project) {
            // Créer 3 images par projet
            for ($i = 1; $i <= 3; $i++) {
                $filename = sprintf('img_%d_%d.jpg', $project->getId(), $i);
                $url = '/uploads/' . $filename;
                $image = new Image();
                $image->setUrl($url);
                $image->setDisplayOrder($i);
                $image->setProject($project);

                $manager->persist($image);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ProjectFixture::class,
        ];
    }
}