<?php

namespace App\Services\Name;

use BitAndBlack\Syllable\Hyphen\Text;
use BitAndBlack\Syllable\Syllable;
use phpDocumentor\Reflection\DocBlock\ExampleFinder;

class Name
{
    private $name;
    private $reportList;

    private $modelName;
    private static $trueName;

    private $notes;

    public function __construct(string $name, string $modelName="")
    {
        $this->name=$name;
        $this->notes=[];

        $this->setModelName($modelName);
    }

    public function makeReport()
    {
        //first place on list
        if (preg_match('#[abc]#', $this->name[0])) {
            $this->setReport('position',"Commence par un ".$this->name[0].". Positionné en tête de listes alphabetiques.");
            $this->notes['position']=2;
        }

        //spelling
        $shortName=$this->name[0];
        for ($i=1; $i < strlen($this->name); $i++) {
            if ($this->name[$i] != substr($shortName,-1,1)) {
                $shortName.=$this->name[$i];
            }
        }

        $matches=[];
        if (preg_match("#([b-df-hj-np-tv-z]{3})#", $shortName, $matches)) {
            $this->setReport('spelling',"La suite de lettres (".$matches[0].") peut être difficile à lire. Il convient pour un usage écrit mais est 
            à éviter si vous prévoyez de l'utiliser à l'oral. Il peut gêner les commentateurs lors de concours.");
        } elseif (preg_match("#[1-9]#", $shortName)) {
            $this->setReport('spelling',"Ce nom contient des chiffres qui peut être difficile à lire. Il convient pour un usage écrit mais est 
            à éviter si vous prévoyez de l'utiliser à l'oral. Il peut gêner les commentateurs lors de concours.");

        } else {
            $this->setReport('spelling',"Ce nom semble facilement prononçable.");
            $this->notes['spelling']=2;
        }

        //syllable count
        $syllable = new Syllable(
            'fr',
            $_SERVER['DOCUMENT_ROOT'].'../src/Services/Word/languages',
            $_SERVER['DOCUMENT_ROOT'].'../src/Services/Word/cache',
            new Text('-')
        );
        $syllablesList=$syllable->splitWord($this->name);
        if (count($syllablesList) == 1 ) {
            //var_dump($syllablesList);
            $this->setReport('memory',"Avec 1 syllabe, ce nom est facilement mémorisable.");
            $this->notes['memory']=2;
        } elseif (count($syllablesList) == 2 ) {
            $this->setReport('memory',"Avec 2 syllabes, ce nom est facilement mémorisable.");
            $this->notes['memory']=2;
        } elseif (count($syllablesList) > 3 ) {
            $this->setReport('memory',"Avec ".count($syllablesList)." syllabes, ce nom est difficilement mémorisable.");
        }

        //similarities with true name
        if ( levenshtein($this->name, self::$trueName) < 5 ) {
            $this->reportList['trueName']="Ce pseudonyme a une ecriture proche du nom que vous avez fourni";
            $this->notes['writtenSimilarities']=2;
        }

    }

    public function addReport(string $report)
    {
        $this->reportList[]=$report;
    }

    public function setReport(string $key, string $report)
    {
        $this->reportList[$key]=$report;
    }

    public function setModelName(string $modelName)
    {
        $this->modelName=$modelName;

        if ($modelName=="allemand") {
            $this->reportList['language-prefix']="Ce pseudonyme à consonnance allemande peut recevoir un prefixe tel que 'von' pour devenir 'von ".$this->name."'";
        }

        if ($modelName=="japonais") {
            $this->reportList['language-suffix']="Ce pseudonyme à consonnance japonaise peut recevoir un suffixe tel que 'san' pour devenir '".$this->name." san'";
        }

    }

    public static function setReferenceText(string $trueName)
    {
        self::$trueName=$trueName;
    }

    public function getReportList() : array
    {
        $this->makeReport();
        return $this->reportList;
    }

    public function getNote() : float
    {
        $this->makeReport();
        $globalNote=array_product($this->notes);
        return $globalNote;
    }

    public function getStandardNote() : float
    {
        $globalNote=$this->getNote();
        $globalNote=round (10 * ( 1 - exp (-1 * $globalNote / 4)) ,1);
        return $globalNote;
    }

    public function __toString() : string
    {
        return $this->name;
    }


}