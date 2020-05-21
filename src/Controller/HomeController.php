<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\WordModels\UniLetterWordModel;
use App\Entity\WordModels\BiLetterWordModel;
use App\Entity\WordModels\TriLetterWordModel;
use App\Entity\WordModels\BiSyllableWordModel;

use App\Repository\WordModelRepository;

use App\Services\Files\VariousFileTools;

use App\Services\StringBeautifyer\StringBeautifyer;

/**
 * Class HomeController
 *
 * @Route("home/", name="home_")
 */
class HomeController extends AbstractController
{

    const USES=["consultant", "gamer"];
    private $languages;

    public function __construct()
    {
        $this->languages=WordModelRepository::getAvailableLanguages();
    }

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
        // check data
        $dataAndErrors=$this->validateFromGet();
        $data=$dataAndErrors['data'];
        $errors=$dataAndErrors['errors'];
        $isFormValid=$dataAndErrors['validate'];

        $results=[];

        //if everything is fine with data
        if ($isFormValid){

            if (method_exists($this, $data['use'])) {
                $results=call_user_func_array([$this, $data['use']], array($number, $data, $model));
            } else {
                /*
                $wordModel=null;
                //select a model depending on the route
                if ($model == "uni") {
                    $wordModel = new UniLetterWordModel("../assets/dictionnary/".$data['language'].".txt");
                } elseif ($model == "bi") {
                    $wordModel = new BiLetterWordModel("../assets/dictionnary/".$data['language'].".txt");
                } elseif ($model == "tri") {
                    $wordModel = new TriLetterWordModel("../assets/dictionnary/".$data['language'].".txt");
                } elseif ($model == "biSyl") {
                    $wordModel = new BiSyllableWordModel("../assets/dictionnary/".$data['language'].".txt");
                }*/
                $wordModel=WordModelRepository::getWordModel($model, $data['language']);
                $results=$wordModel->generateWords($number, $data['length']);
                $this->setReferenceText($results, $data['name']);
            }
        }

        //render and build response
        return $this->render('home/index.html.twig',
            [
                'results'=>$results,
                'data'=>$data,
                'errors'=>$errors,
                'languages' => $this->languages,
                'uses' => self::USES,
            ]
        );
    }

    private function setReferenceText(array $results, $referenceText) : array
    {
        foreach($results as $result) {
            $result->setReferenceText($referenceText);
        }

        return $results;
    }

    private function consultant(int $number, array $data, string $model) :array
    {
        $results=[];

        $results=array_merge($results,StringBeautifyer::beautifyWithSpecial($data['name'],$number));
        $this->setReferenceText($results, $data['name']);
        return $results;
    }

    private function gamer(int $number, array $data, string $model) :array
    {
        $results1=[];

        $data['language']="japonais";
        /*if ($model == "uni") {
            $wordModel = new UniLetterWordModel($data['language']);
        } elseif ($model == "bi") {
            $wordModel = new BiLetterWordModel($data['language']);
        } elseif ($model == "tri") {
            $wordModel = new TriLetterWordModel($data['language']);
        } elseif ($model == "biSyl") {
            $wordModel = new BiSyllableWordModel($data['language']);
        }*/
        $wordModel=WordModelRepository::getWordModel($model, $data['language']);
        $toBeDone=intdiv($number,2);
        $results1=$wordModel->generateWords($toBeDone, $data['length']);

        $data['language']="allemand";
        /*if ($model == "uni") {
            $wordModel = new UniLetterWordModel($data['language']);
        } elseif ($model == "bi") {
            $wordModel = new BiLetterWordModel($data['language']);
        } elseif ($model == "tri") {
            $wordModel = new TriLetterWordModel($data['language']);
        } elseif ($model == "biSyl") {
            $wordModel = new BiSyllableWordModel($data['language']);
        }*/
        $wordModel=WordModelRepository::getWordModel($model, $data['language']);
        $results2=$wordModel->generateWords($number-$toBeDone, $data['length']);
        $results=array_merge($results1,$results2);

        $this->setReferenceText($results, $data['name']);

        return $results;
    }
/*
    private function getAvailableLanguages() : array
    {
        $path=$_SERVER['DOCUMENT_ROOT'].'../assets/dictionnary/';
        $languages=VariousFileTools::getAvailableFiles($path);
        return array_map(function($x){return substr($x,0,-4);}, $languages);
    }
*/
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
        $key='language';
        if (isset($_GET[$key])) {
            $requestedLanguage=trim($_GET[$key]);
            if (in_array($requestedLanguage, $this->languages)) {
                $data[$key]=$requestedLanguage;
            } else {
                $errors[$key]="la langue doit être comprise dans la liste ". implode(",", $this->languages);
            }
        } else {
            $data[$key]=$this->languages;
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

        // USE CASE
        $key='name';
        if (isset($_GET[$key])) {
            $requestedValue=trim($_GET[$key]);
            if (!empty($requestedValue)) {
                $data[$key]=$requestedValue;
            } else {
                $errors[$key]="Vous devez indiquer un prénom ou un nom";
                $validate=false;
            }
        } else {
            $errors[$key]="Vous devez indiquer un prénom ou un nom";
            $validate=false;
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
}