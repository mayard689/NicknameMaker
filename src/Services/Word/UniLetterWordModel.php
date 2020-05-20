<?php

namespace App\Services\Word;

class UniLetterWordModel extends AbstractWordModel
{
    protected $wordList;
    protected $letterList;
    protected $letterListCount;

    public function setWordList($wordList) : void
    {
        $this->wordList=$wordList;
        $this->letterListCount=$this->getLetterList($wordList);
        $this->letterList=array_keys($this->letterListCount);

    }

    public function getBiLetterStats()
    {
        return $this->biLetterStats;
    }

    public function generateWords(int $number, int $length) : array
    {
        $words=[];

        for ($i=0;$i<$number;$i++) {
            $word="";

            for ($j=0;$j<$length;$j++) {
                $letterIndex= rand(0, array_sum($this->letterListCount) - 1);
                $cumulative=0;
                foreach ($this->letterListCount as $letter=> $letterOccurences) {
                    $cumulative+=$letterOccurences;
                    if ($cumulative>$letterIndex) {
                        $word.=$letter;
                        break;
                    }
                }
            }

            $words[]=$word;
        }

        return $words;
    }

      /**
     * return the lsit of letter used in the $words list
     * we give an array of string, it return an array of letters
     * @param array $words
     * @return array
     */
    private function getLetterList(array $words) : array
    {
        $total=implode("", $words)." ";
        $total = str_replace( array( "\n", "\r" ), array( '', '' ), $total );
        return array_count_values(str_split($total,1));
    }

}