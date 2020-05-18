<?php

namespace App\Services\Word;

class WordModel
{

    protected $stats;
    protected $wordList;

    public function __construct($wordList)
    {
        $this->wordList=$wordList;
        $this->stats=$this->makeStat($wordList);
    }

    public function getStats()
    {
        return $this->stats;
    }

    public function generateWords(int $number, int $length)
    {
        $words=[];

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

            $words[]=$word;
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
            for ($i=1; $i<strlen($word)-1;$i++) {
                $letter=$word[$i];
                $previous=$word[$i-1];

                if (array_key_exists($previous, $stat)) {
                    if (array_key_exists($letter, $stat[$previous])) {
                        $stat[$previous][$letter]++;
                        $stat[$previous]['sum']++;
                    } else {
                        $stat[$previous][$letter]=1;
                        $stat[$previous]['sum']=1;
                    }
                } else {
                    $stat[$previous][$letter]=1;
                    $stat[$previous]['sum']=1;
                }
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