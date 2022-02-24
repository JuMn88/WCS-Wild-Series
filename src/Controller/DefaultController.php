<?php
// src/Controller/DefaultController.php
namespace App\Controller;

use App\Repository\ProgramRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="app_index")
     */
    public function index(ProgramRepository $programRepository): Response
    {
        return $this->render('/index.html.twig', [
            'programs' => $programRepository->findBy(
                [],
                ['id' => 'DESC'],
                3
            )
        ]);
    }

    /**
     * @Route("/change-locale/{locale}", name="app_change_locale")
     */
    public function ChangeLocale(Request $request, string $locale): Response
    {
        //setting the new language according to user's choice
        $request->getSession()->set('_locale', $locale);
        
        //updating the url with the proper prefix
        $urlOrigin = $urlDestination = '';
        $pattern = '/\/fr\/|\/en\//'; //setting the pattern corresponding to the url prefixes (i.e. the languages available on the website)
        $urlOrigin = $request->headers->get('referer'); //where the user is coming from
        $urlDestination = preg_replace($pattern, '/' . $locale . '/', $urlOrigin); //replacing the prefix of the url

        return $this->redirect($urlDestination);
    }

    public function navbarTop(CategoryRepository $categoryRepository): Response
    {
        return $this->render('layout/_navbartop.html.twig', [
            'categories' => $categoryRepository->findBy([], ['id' => 'DESC'])
        ]);
    }
}
