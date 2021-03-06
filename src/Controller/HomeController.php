<?php

namespace App\Controller;

use App\Services\NickNameMaker\WordMerger;
use App\Services\WordModel\WordModelMaker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\WordModels\UniLetterWordModel;
use App\Services\StringBeautifyer\StringBeautifyer;

/**
 * Class HomeController
 *
 */
class HomeController extends AbstractController
{
    const USES=["consultant", "gamer", "nom valise", "nom valise 2"];
    private $languages;

    public function __construct()
    {
        $this->languages=WordModelMaker::getLocalWordModel();
    }

    /**
     *
     * @param array $model
     * @return Response
     *
     * @Route("{model<uni|bi|tri|biSyl>}", name="makeName")
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

            $methodName=str_replace(" ",'',ucwords(trim($data['use'])));

            if (method_exists($this, $methodName)) {
                $results=call_user_func_array([$this, $methodName], array($number, $data, $model));
            } else {
                $wordModel=WordModelMaker::getWordModel($model, $data['language']);
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
        $results=[];

        $wordModel=WordModelMaker::getWordModel($model, "japonais");
        $toBeDone=intdiv($number,2);
        $results=$wordModel->generateWords($toBeDone, $data['length']);

        $wordModel=WordModelMaker::getWordModel($model, "allemand");
        $results2=$wordModel->generateWords($number-$toBeDone, $data['length']);
        $results=array_merge($results,$results2);

        $this->setReferenceText($results, $data['name']);

        return $results;
    }

    private function nomValise(int $number, array $data, string $model) : array
    {
        $wordModel=WordModelMaker::getWordModel($model, []);

        if (!empty($data['inspiration1'])){
            $wordModel1=WordModelMaker::getWordModelByTheme($model, $data['inspiration1']);
            $wordModel->merge($wordModel1,1);
        }

        if (!empty($data['inspiration2'])){
            $wordModel1=WordModelMaker::getWordModelByTheme($model, $data['inspiration2']);
            $wordModel->merge($wordModel1,1);
        }

        if (!empty($data['inspiration3'])){
            $wordModel1=WordModelMaker::getWordModelByTheme($model, $data['inspiration3']);
            $wordModel->merge($wordModel1,1);
        }

        $results=$wordModel->generateWords($number, $data['length']);

        $this->setReferenceText($results, $data['name']);

        return $results;
    }

    private function nomValise2(int $number, array $data, string $model) : array
    {


        if (empty($data['inspiration1']) || empty($data['inspiration2']) || empty($data['inspiration3'])){
            $this->redirectToRoute("");
        }

        $wordMerger= new WordMerger([$data['inspiration1'], $data['inspiration2'], $data['inspiration3']]);
        $results=$wordMerger->generateWords($number);

        $this->setReferenceText($results, $data['name']);

        return $results;
    }

    private function validateFromGet(){

        $_GET=array_map('trim', $_GET);

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

        // NAME
        $this->checkIfExistsInGet(
            'name',
            true,
            "Vous devez indiquer un prénom ou un nom",
            $data, $errors, $validate);

        // INSPIRATION1
        $this->checkIfExistsInGet(
            'inspiration1',
            false,
            null,
            $data, $errors, $validate);
        $this->checkIfExistsInGet(
            'inspiration2',
            false,
            null,
            $data, $errors, $validate);
        $this->checkIfExistsInGet(
            'inspiration3',
            false,
            null,
            $data, $errors, $validate);

        // RETURN
        return ['data' => $data, 'errors' => $errors, 'validate' => $validate];
    }

    private function checkIfExistsInGet($key, $required, $message, &$data, &$errors, &$validate) : array
    {
        if (!empty($_GET[$key])) {
            $data[$key]=$_GET[$key];
        } else {
            $errors[$key]=$message;
            if ($required) {
                $validate=false;
            }
        }
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

