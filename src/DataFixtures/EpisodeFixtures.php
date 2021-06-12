<?php

namespace App\DataFixtures;

use App\Entity\Episode;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class EpisodeFixtures extends Fixture implements DependentFixtureInterface
{
    public const EPISODES = [
        [
            'title' => 'Rick a choppé la COVID',
            'number' => 1,
            'summary' => 'Rick se réveille dans un hôpital après une COVID carabinée.',
            'season' => 'season_0'
        ],
        [
            'title' => 'Rick et les antivax',
            'number' => 2,
            'summary' => 'Rick a maille à se défaire des nombreux antivax tombés malades (ils sont agressifs et ont le teint peu naturel.',
            'season' => 'season_0'
        ],
        [
            'title' => 'Rick et la tribu de Dana',
            'number' => 3,
            'summary' => 'Rick se réfugie dans un camping. Il découvre que sa femme sort maintenant avec son meilleur ami (ça tourne mal).',
            'season' => 'season_0'
        ],
        [
            'title' => 'Rick et les problèmes existentiels',
            'number' => 4,
            'summary' => 'Rick se pose de questions. C\'est mou.',
            'season' => 'season_0'
        ],
        [
            'title' => 'Rick et le boudin noir.',
            'number' => 5,
            'summary' => 'Rick découvre qu\'il peut se camoufler des antivax en s\'enroulant dans du boudin noir. Il se rend con dans une boucherie-charcuterie.',
            'season' => 'season_0'
        ],
    ];
    
    public function load(ObjectManager $manager)
    {
        foreach (self::EPISODES as $key => $episodeInfo) {
            $episode = new Episode();
            $episode->setTitle($episodeInfo['title']);
            $episode->setNumber($episodeInfo['number']);
            $episode->setSummary($episodeInfo['summary']);
            $episode->setSeason($this->getReference($episodeInfo['season']));
            
            $manager->persist($episode);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        // Tu retournes ici toutes les classes de fixtures dont SeasonFixtures dépend
        return [
          SeasonFixtures::class,
        ];
    }
}
