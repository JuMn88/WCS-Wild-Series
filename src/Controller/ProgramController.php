<?php
// src/Controller/ProgramController.php
namespace App\Controller;

use App\Entity\Season;
use App\Entity\Comment;
use App\Entity\Episode;
use App\Entity\Program;
use App\Service\Slugify;
use App\Form\CommentType;
use App\Form\ProgramType;
use Symfony\Component\Mime\Email;
use App\Repository\SeasonRepository;
use App\Controller\CommentController;
use App\Form\SearchProgramFormType;
use App\Repository\EpisodeRepository;
use App\Repository\ProgramRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
    public function index(Request $request, ProgramRepository $programRepository): Response
    {
        //Create the search form
        $form = $this->createForm(SearchProgramFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //Select only the programs with the right title
            $search = $form->getData()['search'];
            $programs = $programRepository->findLikeName($search);
        } else {
            //Select every program
            $programs = $programRepository->findAll();
        }
        
        return $this->render(
            'program/index.html.twig', [
            'programs' => $programs,
            'form' => $form->createView()
        ]);
    }
    /**
     * The controller for the program add form
     * Display the form or deal with it
     *
     * @Route("/new", name="new")
     */
    public function new(Request $request, Slugify $slugify, MailerInterface $mailer) : Response
    {
        // Create a new Program Object
        $program = new Program();
        // Create the associated Form
        $form = $this->createForm(ProgramType::class, $program);
        // Get data from HTTP request
        $form->handleRequest($request);

        // Was the form submitted ?
        if ($form->isSubmitted() && $form->isValid()) {
            // Deal with the submitted data
            // Get the Entity Manager
            $entityManager = $this->getDoctrine()->getManager();

            //Generating the program's slug from its title
            $slug = $slugify->generate($program->getTitle());
            $program->setSlug($slug);

            // Set the program's owner
            $program->setOwner($this->getUser());

            // Persist Category Object
            $entityManager->persist($program);
            // Flush the persisted object
            $entityManager->flush();

            // Once the form is submitted, valid and the data inserted in database, you can define the success flash message
            $this->addFlash('success', 'Cette série a été ajoutée !');

            //Sending an email
            $email = (new Email())
                ->from($this->getParameter('mailer_from'))
                ->to($this->getParameter('mailer_to'))
                ->subject('Une nouvelle série vient d\'être publiée !')
                ->html($this->renderView('email/newProgramEmail.html.twig', ['program' => $program]));
            $mailer->send($email);

            // Finally redirect to categories list
            return $this->redirectToRoute('program_index');
        }
        // Render the form
        return $this->render('program/new.html.twig', [
            "form" => $form->createView(),
        ]);
    }
    /**
     * Getting a program by id and its seasons
     * 
     * @Route("/{slug}", name="show")
     * @return Response
     */
    public function show(Program $program, SeasonRepository $seasonRepository, Slugify $slugify): Response
    {
        $seasons = $seasonRepository->findByProgram($program->getId());
        
        $slug = $program->getSlug();

        if (!$program) {
            throw $this->createNotFoundException(
                'No program with id : '. $program->getId() .' found in program\'s table.'
            );
        }

        return $this->render('program/show.html.twig', [
            'program' => $program,
            'seasons' => $seasons
        ]);
    }
    /**
     * Getting a season of a program by id and its episodes
     * 
     * @Route("/{slug}/seasons/{season<^[0-9]+$>}", name="season_show")
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
     * Getting an episode by slug of a program
     * 
     * @Route("/{programSlug<^[a-zA-Z0-9 \'-]+$>}/seasons/{season<^[0-9]+$>}/episodes/{episodeSlug<^[a-zA-Z0-9 \'-]+$>}", name="episode_show")
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"programSlug": "slug"}})
     * @ParamConverter("episode", class="App\Entity\Episode", options={"mapping": {"episodeSlug": "slug"}})
     * @return Response
     */
    public function showEpisode(
        Program $program, 
        Season $season,
        Episode $episode,
        CommentController $commentController,
        Request $request
        )
    {
        if (!$program) {
            throw $this->createNotFoundException(
                'No program with given id found in program\'s table.'
            );
        }
        if (!$season) {
            throw $this->createNotFoundException(
                'No season with given id found in season\'s table.'
            );
        }
        if (!$episode) {
            throw $this->createNotFoundException(
                'No episode with given id found in season\'s table.'
            );
        }

        if ($this->isGranted('ROLE_CONTRIBUTOR')) {
            
            return $commentController->new($episode, $request);

        } else {

            return $this->render('program/episode_show.html.twig', [
                'program' => $program,
                'season' => $season,
                'episode' => $episode,
            ]);
        }  
    }

    /**
     * @Route("/{programSlug<^[a-zA-Z0-9 \'-]+$>}/edit", name="edit", methods={"GET","POST"})
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"programSlug": "slug"}})
     */
    public function edit(Request $request, Program $program): Response
    {
        // Check wether the logged in user is the owner of the program or the admin
        if (!($this->getUser() == $program->getOwner()) && !($this->isGranted('ROLE_ADMIN'))) {
            // If not the owner, throws a 403 Access Denied exception
            throw new AccessDeniedException('Only the owner or the admin can edit the program!');
        }

        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            // Once the form is submitted, valid and the data inserted in database, you can define the success flash message
            $this->addFlash('success', 'Cette série a été correctement éditée !');

            return $this->redirectToRoute('program_index');
        }

        return $this->render('program/edit.html.twig', [
            'program' => $program,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/watchlist", name="watchlist", methods={"GET","POST"})
     * @isGranted("ROLE_CONTRIBUTOR")
     */
    public function addToWatchlist(Request $request, Program $program, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser()->isInWatchlist($program)) {
            $this->getUser()->removeFromWatchlist($program);
        } else {
            $this->getUser()->addToWatchlist($program);
        }
        $entityManager->flush();

        return $this->redirectToRoute('program_show', [
                'program' => $program,
                'slug' => $program->getSlug(),
            ]);

    }
}