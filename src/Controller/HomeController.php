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
     * @return string
     *
     * @Route("makeStat", name="makeStat")
     */
    public function getStat()
    {
        $stats=$this->makeStat(['adrien', 'melanie', 'camille', 'baptiste']);
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

    /**
     * @param array $words
     * @return array
     */
    private function makeStat(array $words) : array
    {
        $letterList=$this->getLetterList($words);

        $stat['keys']=$letterList;
        $stat['keys'][]='sum';

        foreach($letterList as $letterAsColumn) {
            $stat[$letterAsColumn]=  array_fill_keys($letterList,0);
            $stat[$letterAsColumn]['sum']=0;
        }

        foreach ($words as $word) {
            $word=trim($word)." ";
            for ($i=1; $i<strlen($word)-1;$i++) {
                $letter=$word[$i];
                $previous=$word[$i-1];

                if (array_key_exists($previous, $stat)) {
                    if (array_key_exists($letter, $stat[$previous])) {
                        $stat[$previous][$letter]++;
                        $stat[$previous]['sum']++;
                    } else {
                        $stat[$previous][$letter]=1;
                        $stat[$previous]['sum']=1;
                    }
                } else {
                    $stat[$previous][$letter]=1;
                    $stat[$previous]['sum']=1;
                }
            }
        }

        return $stat;
    }

    private function getLetterList(array $words) : array
    {
        $total=implode("", $words)." ";
        return array_unique(str_split($total,1));
    }
}