<?php
// src/DataFixtures/SocialNetworkFixture.php
namespace App\DataFixtures;

use App\Entity\SocialNetwork;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SocialNetworkFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $networks = [
            ['name' => 'GitHub',   'icon' => 'fa-github',   'url' => 'https://github.com/toi'],
            ['name' => 'LinkedIn', 'icon' => 'fa-linkedin', 'url' => 'https://linkedin.com/in/toi'],
            ['name' => 'Twitter',  'icon' => 'fa-twitter',  'url' => 'https://twitter.com/toi'],
        ];

        foreach ($networks as $data) {
            $sn = new SocialNetwork();
            $sn->setName($data['name']);
            $sn->setIcon($data['icon']);
            $sn->setUrl($data['url']);
            $manager->persist($sn);
        }

        $manager->flush();
    }
}