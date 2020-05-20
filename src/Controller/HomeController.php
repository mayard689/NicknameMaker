<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Services\Word\UniLetterWordModel;
use App\Services\Word\BiLetterWordModel;
use App\Services\Word\TriLetterWordModel;
use App\Services\Word\BiSyllableWordModel;

/**
 * Class HomeController
 *
 * @Route("home/", name="home_")
 */
class HomeController extends AbstractController
{

    const USES=["consultant", "gamer"];
    private $sound=[];

    /**
     * Return the index file with "adrien" and "mélanie" as proposed nicknames
     * @return string
     *
     * @Route("index", name="index")
     */
    public function index()
    {
        return $this->render('home/index.html.twig');
    }

    /**
     * Return the index with 3 random propositions 10 characters long similar to the givne examples
     * @param array $model
     * @return Response
     *
     * @Route("makeName/{model<uni|bi|tri|biSyl>}", name="makeName")
     */
    public function makeName($model="tri", int $number=10)
    {

        //get available languages
        $languages= ["français", "japonais", "allemand", "beaute", "gamer"];

        // check data
        $dataAndErrors=$this->validateFromGet();
        $data=$dataAndErrors['data'];
        $errors=$dataAndErrors['errors'];

        //select a model depending on the route
        $wordModel=null;
        if ($model == "uni") {
            $wordModel = new UniLetterWordModel("../assets/dictionnary/".$data['language'].".txt");
        } elseif ($model == "bi") {
            $wordModel = new BiLetterWordModel("../assets/dictionnary/".$data['language'].".txt");
        } elseif ($model == "tri") {
            $wordModel = new TriLetterWordModel("../assets/dictionnary/".$data['language'].".txt");
        } elseif ($model == "biSyl") {
            $wordModel = new BiSyllableWordModel("../assets/dictionnary/".$data['language'].".txt");
        }

        //generate words
        $results=$wordModel->generateWords($number, $data['length']);

        //render and build response
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
        $acceptedLanguages=["français", "japonais",  "allemand", "beaute","gamer"];
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
        //$wordModel = new UniLetterWordModel($words);
        $wordModel = new UniLetterWordModel("../assets/dictionnary/beaute.txt");
        $stats=$wordModel->getBiLetterStats();
        return $this->render('home/stat.html.twig', ['stats'=>$stats]);

    }

    private function generateNickNameFromName(string $name) : array {

        $maleChar=['&','#','~','`','\\','@','!','§','£','$',':','<','>','|','°','%','*','+'];
        $femaleChar=[];
        $accentAndSpecialChar=[
            'i'=>['î','ï', 'í', 'ì','1'],
            'e'=>['ê', 'é', 'è', 'ë','€'],
            'a'=>['å', 'à', 'á', 'â', 'ã', 'ä', 'æ','@','ª'],
            'o'=>['ò', 'ó', 'ô', 'õ', 'ö', 'œ','0','Ø', 'º','°'],
            'u'=>['ù', 'ú', 'û', 'ü'],
            'y'=>['ý', 'ÿ'],
            'f'=>['ƒ'],
            's'=>['š','$'],
            'z'=>['ž'],
            'c'=>['©','ç'],
            'r'=>['®'],
        ];
        $braces=['()','{}','[]','``','--','~~','<>','><','//','\\\\','/\\','\\/','**','%%','&&'];

    }


}