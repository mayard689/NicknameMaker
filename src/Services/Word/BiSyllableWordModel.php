<?php

namespace App\Services\Word;

use BitAndBlack\Syllable\Syllable;
use BitAndBlack\Syllable\Hyphen\Text;

class BiSyllableWordModel extends AbstractWordModel
{

    protected $letterList;
    protected $letterListCount;
    protected $syllablesStats;
    protected $wordsAsSyllables;

    const MAX_WORDLIST_SIZE_FOR_SYLLABLE_ANALYSIS=100;

    public function setWordList($wordList) : void
    {
            $this->wordsAsSyllables=$this->getWordsAsSyllables($wordList);
            $this->syllablesStats=$this->makeStatFromBiSyllables($this->wordsAsSyllables);
    }

    public function generateWords(int $number, int $length, bool $withoutSpace=true) : array
    {
        //if syllable statistics are available
        $words=[];

        //add 1 char since we want to add a space at the end
        $length++;

        for ($i=0;$i<$number;$i++) {
            $word="";
            $generativeSyllable=" ";
            for ($j=0;$j<$length;$j++) {
                $numberOfPossibilities=$this->syllablesStats[$generativeSyllable]['sum'];
                $syllableIndex=rand(0, $numberOfPossibilities - 1);
                $cumulative=0;
                foreach ($this->syllablesStats[$generativeSyllable] as $syllable=> $syllableOccurences) {
                    $cumulative+=$syllableOccurences;
                    if ($cumulative>$syllableIndex) {
                        $word.=$syllable;
                        $generativeSyllable=$syllable;
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

    private function makeStatFromBiSyllables($wordsAsSyllablesArray) : array
    {
        $syllablesList=$this->getSyllableList($wordsAsSyllablesArray);

        //build the array titles in the first 2nd dimension array
        $stat['keys']=$syllablesList;
        $stat['keys'][]='sum';

        //make other 2nd dimension array full of 0
        foreach($syllablesList as $syllable) {
            $stat[$syllable]=  array_fill_keys($syllablesList,0);
            $stat[$syllable]['sum']=0;
        }

        //replace 0 by the true values
        foreach ($wordsAsSyllablesArray as $wordAsSyllablesArray) {
            for ($i=0; $i<count($wordAsSyllablesArray);$i++) {
                $currentSyllable=$wordAsSyllablesArray[$i];
                $previousSyllable = $i==0 ? " " : $wordAsSyllablesArray[$i-1];
                $stat[$previousSyllable][$currentSyllable]++;
                $stat[$previousSyllable]['sum']++;
            }

            $currentSyllable=" ";
            $previousSyllable = end($wordAsSyllablesArray);
            $stat[$previousSyllable][$currentSyllable]++;
            $stat[$previousSyllable]['sum']++;
        }

        return $stat;
    }

     /**
     * return the list of all syllables used in the model word list
     * we give an array of syllable array, it return an array of syllables (string)
     * @param array $syllablesList
     * @return array
     */
    private function getSyllableList(array $syllablesList) : array
    {
        $syllablesAsString=[];
        foreach ( $syllablesList as $word) {
            $syllablesAsString=array_merge($syllablesAsString,$word);
        }
        $syllablesAsString[]=" ";

        return array_unique($syllablesAsString);
    }

    /**
     * replace each word as an array of syllables
     * @param $words
     * @return array
     * @throws \BitAndBlack\Syllable\Exception\DirNotReadableException
     */
    private function getWordsAsSyllables($words) :array {
        $syllable = new Syllable(
            'fr',
            dirname(__FILE__).'/languages',
            dirname(__FILE__).'/cache',
            new Text('-')
        );

        $syllablesList=[];
        foreach ( $words as $word) {
            $word = str_replace( array( "\n", "\r" ), array( '', '' ), $word );
            $syllablesList[]=$syllable->splitWord($word);
        }

        return $syllablesList;
    }
}