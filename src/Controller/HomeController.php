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

    const USES=["consultant", "gamer"];

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
    public function makeNameAccordingToModel(int $length=7, int $number=10)
    {

        //get available languages
        $languages= ["français", "japonais"];

        // check data
        $dataAndErrors=$this->validateFromGet();
        $data=$dataAndErrors['data'];
        $errors=$dataAndErrors['errors'];

        $wordModel = new WordModel("../assets/dictionnary/".$data['language'].".txt");
        $results=$wordModel->generateWords($number, $length);
        return $this->render('home/index.html.twig',
            [
                'results'=>$results,
                'data'=>$data,
                'errors'=>$errors,
                'languages' => $languages,
                'uses' => self::USES,
            ]
        );
    }

    private function validateFromGet(){

        $data=[];
        $errors=[];
        $validate=true;

        // LENGTH
        $minLength=2;
        $maxLength=12;

        if (isset($_GET['length'])) {
            $requestedLength=intval(trim($_GET['length']));
            if (is_numeric($requestedLength) && $requestedLength<=$maxLength && $requestedLength>=$minLength) {
                $data['length']=intval($requestedLength);
            } else {
                $errors['length']="La longueur doit être un nombre compris entre $minLength et $maxLength caractères.";
            }
        } else {
            $data['length']=7;
        }

        // LANGUAGE
        $acceptedLanguages=["français", "japonais"];
        $key='language';
        if (isset($_GET[$key])) {
            $requestedLanguage=trim($_GET[$key]);
            if (in_array($requestedLanguage, $acceptedLanguages)) {
                $data[$key]=$requestedLanguage;
            } else {
                $errors[$key]="la langue doit être comprise dans la liste ". implode(",", $acceptedLanguages);
            }
        } else {
            $data[$key]=$acceptedLanguages[0];
        }

        // USE CASE
        $acceptedList=self::USES;
        $key='use';
        if (isset($_GET[$key])) {
            $requestedValue=trim($_GET[$key]);
            if (in_array($requestedValue, $acceptedList)) {
                $data[$key]=$requestedValue;
            } else {
                $errors[$key]="le cas d'usage doit être compris dans la liste ". implode(",", $acceptedList);
            }
        } else {
            $data[$key]=$acceptedList[0];
        }

        // RETURN
        return ['data' => $data, 'errors' => $errors, 'validate' => $validate];
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
        //$wordModel = new WordModel($words);
        $wordModel = new WordModel("../assets/dictionnary/français.txt");
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