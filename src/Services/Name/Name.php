<?php

namespace App\Services\Name;

class Name
{
    private $name;
    private $reportList;

    private $language;

    private $notes;

    public function __construct(string $name)
    {
        $this->name=$name;
        $this->notes=[];

        $this->makeReport();
    }

    public function makeReport()
    {
        //first place on list
        if (preg_match('#[abc]#', $this->name[0])) {
            $this->setReport('first',"Ce nom commençant par une des premières lettres de l'alphabet à l'avantage de se positionner en début de liste. Ceci est intéréssant pour améliorer sa visibilité dansl es systèmes de rechercher");
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
            $this->setReport('spelling',"Ce nom contient une suite de lettres (".$matches[0][0].") qui peut être difficile à lire. Il convient pour un usage écrit mais est 
            à éviter si vous prévoyez de l'utiliser à l'oral. Il peut gêner les commentateurs lors de concours.");
        } elseif (preg_match("#[1-9]#", $shortName)) {
            $this->setReport('spelling',"Ce nom contient des chiffres qui peut être difficile à lire. Il convient pour un usage écrit mais est 
            à éviter si vous prévoyez de l'utiliser à l'oral. Il peut gêner les commentateurs lors de concours.");

        } else {
            $this->setReport('spelling',"Ce nom semble facilement pronoçable.");
            $this->notes['spelling']=2;
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

    public function setLanguage(string $language)
    {
        $this->language=$language;

        if ($language=="allemand") {
            $this->reportList['language-prefix']="Ce pseudonyme à consonnance allemande peut recevoir un prefixe tel que 'von' pour devenir 'von ".$this->name."'";
        }

        if ($language=="japonais") {
            $this->reportList['language-suffix']="Ce pseudonyme à consonnance japonaise peut recevoir un suffixe tel que 'san' pour devenir '".$this->name." san'";
        }

    }

    public function setTrueName(string $trueName)
    {
        $this->trueName=$trueName;

        if ( levenshtein($this->name, $trueName) < 5 ) {
            $this->reportList['language-suffix']="Ce pseudonyme a une ecriture proche du nom que vous avez fourni";
            $this->notes['writtenSimilarities']=2;
        }


    }

    public function __toString() : string
    {
        return $this->name;
    }

}