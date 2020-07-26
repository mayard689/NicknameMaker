<?php

namespace App\Entity\WordModels;

use App\Services\Name\Name;

abstract class AbstractWordModel
{
    protected $wordList;
    protected $modelName;

    public function __construct(array $wordList)//array $wordList
    {
        $this->setWordList($wordList);
    }

    public function getWordList() : array
    {
        return $this->wordList;
    }

    public function setModelName(string $modelName)
    {
        $this->modelName=$modelName;
    }

    public abstract function setWordList($wordList) : void;

    public abstract function generateWords(int $number, int $length) : array;

    public function merge(AbstractWordModel $otherWordModel, int $force)
    {
        for($i=0; $i<$force; $i++){
            $this->wordList=array_merge($this->wordList, $otherWordModel->wordList);
        }
        $this->setWordList($this->wordList);
    }
}