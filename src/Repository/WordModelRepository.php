<?php

namespace App\Repository;

use App\Entity\WordModels\AbstractWordModel;
use App\Entity\WordModels\UniLetterWordModel;
use App\Entity\WordModels\BiLetterWordModel;
use App\Entity\WordModels\TriLetterWordModel;
use App\Entity\WordModels\BiSyllableLetterWordModel;

use App\Services\Files\VariousFileTools;

class WordModelRepository
{

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
            $path="../assets/dictionnary/".$wordList.".txt";
            $file = fopen($path, 'rb');
            $wordList=[];
            while(!feof($file)) {
                $line = fgets($file);
                $wordList[]=strtolower($line);
            }
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

    public static function getAvailableLanguages() : array
    {
        $path=$_SERVER['DOCUMENT_ROOT'].'../assets/dictionnary/';
        $languages=VariousFileTools::getAvailableFiles($path);
        return array_map(function($x){return substr($x,0,-4);}, $languages);
    }
}