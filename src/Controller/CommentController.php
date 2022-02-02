<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Episode;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("/comments", name="comment_")
 */
class CommentController extends AbstractController
{
    /**
     * @Route("/new", name="new", methods={"POST"})
     * @isGranted("ROLE_CONTRIBUTOR")
     */
    public function new(Episode $episode, Request $request): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $comment->setEpisode($episode);
            $comment->setAuthor($this->getUser());
            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Votre commentaire a été posté !');

            return $this->redirectToRoute('program_episode_show', [
                'programSlug' => $episode->getSeason()->getProgram()->getSlug(),
                'season' => $episode->getSeason()->getId(),
                'episodeSlug' => $episode->getSlug(),
            ],
            Response::HTTP_SEE_OTHER
        );
        }

        return $this->render('program/episode_show.html.twig', [
            'program' => $episode->getSeason()->getProgram(),
            'season' => $episode->getSeason(),
            'episode' => $episode,
            'comment' => $comment,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{commentId}", name="delete", methods={"POST"})
     * @ParamConverter("comment", class="App\Entity\Comment", options={"mapping": {"commentId": "id"}})
     */
    public function delete(Request $request, Comment $comment): Response
    {
        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $comment->setComment('Ce commentaire a été supprimé par son auteur/autrice ou par la modération.');
            // $entityManager->remove($comment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('program_episode_show', [
            'programSlug' => $comment->getEpisode()->getSeason()->getProgram()->getSlug(),
            'season' => $comment->getEpisode()->getSeason()->getId(),
            'episodeSlug' => $comment->getEpisode()->getSlug(),
        ],
        Response::HTTP_SEE_OTHER
    );
    }
}
