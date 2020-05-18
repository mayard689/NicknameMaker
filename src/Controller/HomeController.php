<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class HomeController
 *
 * @Route("home/", name="home_")
 */
class HomeController extends AbstractController
{

    /**
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