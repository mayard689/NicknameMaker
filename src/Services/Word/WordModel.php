<?php

namespace App\Services\Word;

class WordModel
{

    protected $stats;
    protected $wordList;

    public function __construct()//array $wordList
    {
        $wordList=[];

        $args = func_get_args();

        if (is_array($args[0])) {
            $wordList=$args[0];
        } elseif (is_string($args[0])) {
            $file = fopen($args[0], 'rb');
            while(!feof($file)) {
                $line = fgets($file);
                $wordList[]=$line;
            }
        }

    $this->wordList=$wordList;
    $this->stats=$this->makeStat($wordList);

    }



    public function getStats()
    {
        return $this->stats;
    }

    public function generateWords(int $number, int $length, bool $withoutSpace=true)
    {
        $words=[];

        //add 1 char since we want to add a space at the end
        $length++;

        for ($i=0;$i<$number;$i++) {
            $word="";
            $generativeCharacter=" ";
            for ($j=0;$j<$length;$j++) {
                $numberOfPossibilities=$this->stats[$generativeCharacter]['sum'];
                $letterIndex=rand(0, $numberOfPossibilities - 1);
                $cumulative=0;
                foreach ($this->stats[$generativeCharacter] as $letter=>$letterOccurences) {
                    //var_dump($this->stats); var_dump($generativeCharacter); var_dump($letter);exit();
                    $cumulative+=$letterOccurences;
                    if ($cumulative>$letterIndex) {
                        $word.=$letter;
                        $generativeCharacter=$letter;
                        break;
                    }
                }
            }

            //if the is no space into the word or if space are allowed
            if((strpos(trim($word), " "))==false || (!$withoutSpace)) {
                //if the words ends with a space (finish as words in the given list)
                if (substr($word,-1)==" ") {
                    $words[]=$word;
                } else {
                    $i--;
                }
            } else {
                $i--;
            }

        }

        return $words;
    }

    /**
     * @param array $words
     * @return array
     */
    private function makeStat(array $words) : array
    {
        $letterList=$this->getLetterList($words);

        $stat['keys']=$letterList;
        $stat['keys'][]='sum';

        foreach($letterList as $letterAsColumn) {
            $stat[$letterAsColumn]=  array_fill_keys($letterList,0);
            $stat[$letterAsColumn]['sum']=0;
        }

        foreach ($words as $word) {
            $word=" ".trim($word)." ";
            for ($i=1; $i<strlen($word);$i++) {
                $letter=$word[$i];
                $previous=$word[$i-1];

                $stat[$previous][$letter]++;
                $stat[$previous]['sum']++;
            }
        }

        return $stat;
    }

    /**
     * @param array $words
     * @return array
     */
    private function getLetterList(array $words) : array
    {
        $total=implode("", $words)." ";
        return array_unique(str_split($total,1));
    }
}