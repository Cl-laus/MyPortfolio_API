<?php
// src/DataFixtures/InformationFixture.php
namespace App\DataFixtures;

use App\Entity\Information;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class InformationFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $info = new Information();
        $info->setFullName('John Doe');
        $info->setJobTitle('Développeur Full Stack');
        $info->setTagLine('Je crée des applications web modernes');
        $info->setIntroText('Passionné par le développement web, je travaille avec Symfony et React.');
        $info->setPhotoPath(null);
        $info->setAboutTitle('À propos de moi');
        $info->setAboutText('Reconverti dans le développement web, j\'aime créer des projets de A à Z.');
        $info->setEmail('john@example.com');
        $info->setCv(null);

        $manager->persist($info);
        $manager->flush();
    }
}