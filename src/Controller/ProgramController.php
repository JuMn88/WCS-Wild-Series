<?php
// src/Controller/ProgramController.php
namespace App\Controller;

use App\Entity\Program;
use App\Entity\Season;
use App\Entity\Episode;
use App\Repository\ProgramRepository;
use App\Repository\SeasonRepository;
use App\Repository\EpisodeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
* @Route("/programs", name="program_")
*/
class ProgramController extends AbstractController
{
    /**
     * Show all rows from Program's entity
     * 
     * @Route("/", name="index")
     * @return Response A response instance
     */
    public function index(): Response
    {
        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findAll();
        
        return $this->render(
            'program/index.html.twig', [
            'programs' => $programs
        ]);
    }
    /**
     * Getting a program by id and its seasons
     * 
     * @Route("/show/{id<^[0-9]+$>}", name="show")
     * @return Response
     */
    public function show(Program $program, SeasonRepository $seasonRepository): Response
    {
        $seasons = $seasonRepository->findByProgram($program->getId());

        return $this->render('program/show.html.twig', [
            'program' => $program,
            'seasons' => $seasons
        ]);
    }
    /**
     * Getting a season of a program by id and its episodes
     * 
     * @Route("/{program<^[0-9]+$>}/seasons/{season<^[0-9]+$>}", name="season_show")
     * @return Response
     */
    public function showSeason(
        Program $program, 
        Season $season,
        EpisodeRepository $episodeRepository
        )
    {
        $episodes = $episodeRepository->findBySeason($season->getId());
        if (!$episodes) {
            throw $this->createNotFoundException(
                'No episode with season id : '.$season->getId().' found in program\'s table.'
            );
        }

        return $this->render('program/season_show.html.twig', [
            'program' => $program,
            'season' => $season,
            'episodes' => $episodes
        ]);
    }
    /**
     * Getting an episode by id of a program
     * 
     * @Route("/{program<^[0-9]+$>}/seasons/{season<^[0-9]+$>}/episodes/{episode<^[0-9]+$>}", name="episode_show")
     * @return Response
     */
    public function showEpisode(
        Program $program, 
        Season $season,
        Episode $episode
        )
    {
        return $this->render('program/episode_show.html.twig', [
            'program' => $program,
            'season' => $season,
            'episode' => $episode
        ]);
    }
}