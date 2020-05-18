<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Services\Word\WordModel;

/**
 * Class HomeController
 *
 * @Route("home/", name="home_")
 */
class HomeController extends AbstractController
{

    /**
     * Return the index file with "adrien" and "mélanie" as proposed nicknames
     * @return string
     *
     * @Route("index", name="index")
     */
    public function index()
    {
        $results=['adrien',"mélanie"];
        return $this->render('home/index.html.twig', ['results'=>$results]);

    }

    /**
     * Return the index with 3 random propositions 10 characters long
     * @return string
     *
     * @Route("makeName", name="makeName")
     */
    public function makeName()
    {
        $results=$this->generateNickName(3, 10);
        return $this->render('home/index.html.twig', ['results'=>$results]);
    }

    /**
     * Return the index with 3 random propositions 10 characters long similar to the givne examples
     * @param array $model
     * @return Response
     *
     * @Route("makeModeledName", name="makeModeledName")
     */
    public function makeNameAccordingToModel(int $length=10, int $number=3, array $model=['adrien', 'melanie', 'camille', 'baptiste'])
    {
        $wordModel = new WordModel($model);
        $results=$wordModel->generateWords($number, $length);
        return $this->render('home/index.html.twig', ['results'=>$results]);
    }

    /**
     * return a view with the statistics of the given wordlist
     * <return a view to the
     * @return string
     *
     * @Route("showStats", name="showStats")
     */
    public function getStat(array $words=['adrien', 'melanie', 'camille', 'baptiste'])
    {
        $wordModel = new WordModel($words);
        $stats=$wordModel->getStats();
        return $this->render('home/stat.html.twig', ['stats'=>$stats]);

    }

    /**
     * @param int $number : number of nickname to make
     * @return array
     */
    private function generateNickName(int $number, int $length) : array
    {
        $nicknames=[];

        $availableCharacters='azertyuiopmlkjhgfdsqwxcvbn';
        $availableCharactersNumbers=strlen($availableCharacters);

        for ($i=0;$i<$number;$i++) {
            $nickname="";

            for ($j=0;$j<$length;$j++) {
                $nickname .= $availableCharacters[rand(0, $availableCharactersNumbers - 1)];
            }

            $nicknames[]=$nickname;
        }

        return $nicknames;
    }




}