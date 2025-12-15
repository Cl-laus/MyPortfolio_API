<?php
namespace App\DataFixtures;

use App\Entity\Project;
use App\Entity\Technology;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProjectFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $project = new Project();
            $project->setTitle('Project ' . $i);
            $project->setDisplayOrder($i);
            $project->setSummary('Summary for project ' . $i);
            $project->setDescription('Description for project ' . $i);

            // ✅ JSON links correct
            $project->setLinks([
                'github' => 'lien git' . $i,
                'demo'   => 'lien demo' . $i,
            ]);

            // 👇 Association avec 2 technos aléatoires
            $project->addTechnology($this->getReference('tech-0', Technology::class));
            $project->addTechnology($this->getReference('tech-1', Technology::class));

            $project->setCreatedAt(new \DateTimeImmutable());
            $project->setUpdatedAt(new \DateTimeImmutable());

            $manager->persist($project);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TechnologyFixtures::class,
        ];
    }
}
