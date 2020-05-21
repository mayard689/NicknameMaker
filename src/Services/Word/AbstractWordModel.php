<?php

namespace App\Services\Word;

abstract class AbstractWordModel
{
    protected $wordList;
    protected $modelName;

    public function __construct()//array $wordList
    {
        $wordList=[];

        $args = func_get_args();

        if (is_array($args[0])) {
            $wordList=$args[0];
            $this->modelName="freeList";
        } elseif (is_string($args[0])) {
            $this->modelName=$args[0];
            $path="../assets/dictionnary/".$args[0].".txt";
            $file = fopen($path, 'rb');
            while(!feof($file)) {
                $line = fgets($file);
                $wordList[]=strtolower($line);
            }
        }

        $this->setWordList($wordList);
    }

    public function getWordList() : array
    {
        return $this->wordList;
    }

    public abstract function setWordList($wordList) : void;

    public abstract function generateWords(int $number, int $length) : array;
}