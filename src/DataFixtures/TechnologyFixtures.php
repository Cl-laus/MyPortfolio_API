<?php
namespace App\DataFixtures;

use App\Entity\Technology;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TechnologyFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $tech = new Technology();
        $tech->setName('Symfony');
        $tech->setIcon('symfony-icon.png');
        $tech->setCategory('Framework');

        $manager->persist($tech);

        $tech2 = new Technology();
        $tech2->setName('React');
        $tech2->setIcon('react-icon.png');
        $tech2->setCategory('Library');

        $manager->persist($tech2);

        $manager->flush();
    }
}
