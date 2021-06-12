<?php

namespace App\DataFixtures;

use App\Entity\Season;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SeasonFixtures extends Fixture implements DependentFixtureInterface
{
    public const SEASONS = [
        [
            'number' => 1,
            'year' => 2001,
            'description' => 'Super saison.',
            'program' => 'program_0'
        ],
        [
            'number' => 2,
            'year' => 2002,
            'description' => 'Dans la continuité de la saison 1.',
            'program' => 'program_0'
        ],
        [
            'number' => 3,
            'year' => 2003,
            'description' => 'Probablement la meilleure saison de la série',
            'program' => 'program_0'
        ],
        [
            'number' => 4,
            'year' => 2004,
            'description' => 'La série commence à tourner en rond.',
            'program' => 'program_0'
        ],
        [
            'number' => 5,
            'year' => 2005,
            'description' => 'Retour aux sources pour un final de qualité.',
            'program' => 'program_0'
        ],
    ];
    public function load(ObjectManager $manager)
    {
        foreach (self::SEASONS as $key => $seasonInfo) {
            $season = new Season();
            $season->setNumber($seasonInfo['number']);
            $season->setYear($seasonInfo['year']);
            $season->setDescription($seasonInfo['description']);
            $season->setProgram($this->getReference($seasonInfo['program']));
            
            $manager->persist($season);
            $this->addReference('season_' . $key, $season);

        }
        $manager->flush();
    }

    public function getDependencies()
    {
        // Tu retournes ici toutes les classes de fixtures dont SeasonFixtures dépend
        return [
          ProgramFixtures::class,
        ];
    }
}
