<?php

namespace App\Services\WordModel;

use App\Entity\WordModels\AbstractWordModel;
use App\Entity\WordModels\BiSyllableWordModel;
use App\Entity\WordModels\UniLetterWordModel;
use App\Entity\WordModels\BiLetterWordModel;
use App\Entity\WordModels\TriLetterWordModel;


use App\Services\Files\VariousFileTools;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

class WordModelMaker
{

    public static function getWordModelByTheme(string $model, string $theme) : AbstractWordModel
    {
        $client = HttpClient::create();
        $response = $client->request('GET', 'https://www.cnrtl.fr/synonymie/'.$theme);
        $statusCode = $response->getStatusCode();

        $words=[];
        if ($statusCode==200) {
            $content = $response->getContent();

            $crawler = new Crawler($content);
            $crawler = $crawler->filter('.syno_format');

            foreach ($crawler as $domElement) {
                $words[]= $domElement->textContent;
            }
        }

        $wordModel=self::getWordModel($model, $words);
        return $wordModel;
    }

    public static function getWordModel() : AbstractWordModel
    {
        $args = func_get_args();
        $type=$args[0];
        $wordList=$args[1];
        $modelName="";

        if (is_array($wordList)) {
            $wordList=$wordList;
            $modelName="freeList";
        } elseif (is_string($wordList)) {
            $modelName=$wordList;
            $wordList = self::getWordListFromFile($modelName);
        }

        if ($type == "uni") {
            $wordModel = new UniLetterWordModel($wordList);
        } elseif ($type == "bi") {
            $wordModel = new BiLetterWordModel($wordList);
        } elseif ($type == "tri") {
            $wordModel = new TriLetterWordModel($wordList);
        } elseif ($type == "biSyl") {
            $wordModel = new BiSyllableWordModel($wordList);
        }

        $wordModel->setModelName($modelName);

        return $wordModel;
    }

    private static function getWordListFromFile(string $modelName) : array
    {
        $path="../assets/dictionnary/".$modelName.".txt";
        $file = fopen($path, 'rb');
        $wordList=[];
        while(!feof($file)) {
            $line = fgets($file);
            $wordList[]=strtolower($line);
        }
        return $wordList;
    }

    public static function getLocalWordModel() : array
    {
        $path=$_SERVER['DOCUMENT_ROOT'].'../assets/dictionnary/';
        $languages=VariousFileTools::getAvailableFiles($path);
        return array_map(function($x){return substr($x,0,-4);}, $languages);
    }
}
