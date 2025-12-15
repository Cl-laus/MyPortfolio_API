<?php
namespace App\DataFixtures;

use App\Entity\Technology;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TechnologyFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 5; $i++) {
            $tech = new Technology();
            $tech->setName('Technology ' . $i);
            $tech->setIcon('icon' . $i . '.png');
            $tech->setCategory('Category ' . $i);


            $manager->persist($tech);
            $this->addReference('tech-' . $i, $tech);
        }

        $manager->flush();
    }
}
