<?php

namespace App\Entity\WordModels;

use App\Services\Name\Name;

class TriLetterWordModel extends AbstractWordModel
{
    protected $wordList;
    protected $biLetterStats;
    protected $letterList;
    protected $letterListCount;
    protected $triLetterStats;

    public function setWordList($wordList) : void
    {
        $this->wordList=$wordList;
        $this->letterListCount=$this->makeLetterList($wordList);
        $this->letterList=array_keys($this->letterListCount);
        $this->biLetterStats=$this->makeStatFromBiLetters($wordList);
        $this->triLetterStats=$this->makeStatFromTriLetters($wordList);
    }

    public function generateWords(int $number, int $length, bool $withoutSpace=true) : array
    {
        $words=[];

        //add 1 char since we want to add a space at the end
        $length++;

        for ($i=0;$i<$number;$i++) {
            $word="";
            $generativeCharacters=" ";
            $numberOfPossibilities=$this->biLetterStats[$generativeCharacters]['sum'];
            $letterIndex=rand(0, $numberOfPossibilities - 1);
            $cumulative=0;
            foreach ($this->biLetterStats[$generativeCharacters] as $letter=> $letterOccurences) {
                $cumulative+=$letterOccurences;
                if ($cumulative>$letterIndex) {
                    $word=$letter;
                    break;
                }
            }

            //$triLetterStats=$this->makeStatFromTriLetters($this->wordList);
            $generativeCharacters=" ".$word;
            for ($j=0;$j<$length-1;$j++) {
                $numberOfPossibilities=$this->triLetterStats[$generativeCharacters]['sum'];
                $letterIndex=rand(0, $numberOfPossibilities - 1);
                $cumulative=0;
                foreach ($this->triLetterStats[$generativeCharacters] as $letter=> $letterOccurences) {
                    $cumulative+=$letterOccurences;
                    if ($cumulative>$letterIndex) {
                        $word.=$letter;
                        $generativeCharacters=substr($word,-2,2);
                        break;
                    }
                }
            }

            //if the is no space into the word or if space are allowed
            if((strpos(trim($word), " "))==false || (!$withoutSpace)) {
                //if the words ends with a space (finish as words in the given list)
                if (substr($word,-1)==" ") {
                    $words[]=new Name($word, $this->modelName);
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
    private function makeStatFromBiLetters(array $words) : array
    {
        //build the array titles in the first 2nd dimension array
        $stat['keys']=$this->letterList;
        $stat['keys'][]='sum';

        //make other 2nd dimension array full of 0
        foreach($this->letterList as $letter) {
            $stat[$letter]=  array_fill_keys($this->letterList,0);
            $stat[$letter]['sum']=0;
        }

        //replace 0 by the true values
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

    private function makeStatFromTriLetters(array $words) : array
    {
        //build the array titles in the first 2nd dimension array
        $stat['keys']=$this->letterList;//$this->getBiLetterList($words);
        $stat['keys'][]='sum';

        //make other 2nd dimension array full of 0
        $biLetterList=$this->makeBiLetterList($words);
        foreach($biLetterList as $biLetter) {
            $stat[$biLetter]=  array_fill_keys($this->letterList,0);
            $stat[$biLetter]['sum']=0;
        }

        //replace 0 by the true values
        foreach ($words as $word) {
            $word=" ".trim($word);
            for ($i=2; $i<strlen($word);$i++) {
                $currentLetter=$word[$i];
                $previousLetters=$word[$i-2].$word[$i-1];
                $stat[$previousLetters][$currentLetter]++;
                $stat[$previousLetters]['sum']++;
            }

            $currentLetter=" ";
            $previousLetters = substr($word, -2, 2);
            $stat[$previousLetters][$currentLetter]++;
            $stat[$previousLetters]['sum']++;
        }

        return $stat;
    }

     /**
     * return the lsit of letter used in the $words list
     * we give an array of string, it return an array of letters
     * @param array $words
     * @return array
     */
    private function makeLetterList(array $words) : array
    {
        $total=implode("", $words)." ";
        $total = str_replace( array( "\n", "\r" ), array( '', '' ), $total );
        return array_count_values(str_split($total,1));
    }

    private function makeBiLetterList(array $words) : array
    {
        $biLetterList=[];
        foreach ( $words as $word) {
            $word=" ".trim($word)." ";
            for ($i=1; $i<strlen($word); $i++) {
                $previous=$word[$i-1];
                $current=$word[$i];
                $biLetterList[]=$previous.$current;
            }

        }
        $biLetterList[]=" ";

        return $biLetterList;
    }
}