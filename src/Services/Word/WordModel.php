<?php

namespace App\Services\Word;

use BitAndBlack\Syllable\Syllable;
use BitAndBlack\Syllable\Hyphen\Text;

class WordModel
{
    protected $wordList;
    protected $biLetterStats;
    protected $letterList;
    protected $letterListCount;
    protected $syllablesStats;
    protected $syllableList;

    const MAX_WORDLIST_SIZE_FOR_SYLLABLE_ANALYSIS=100;

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
                $wordList[]=strtolower($line);
            }
        }

        $this->setWordList($wordList);


    }

    public function setWordList($wordList)
    {
        $this->wordList=$wordList;
        $this->letterListCount=$this->getLetterList($wordList);
        $this->letterList=array_keys($this->letterListCount);
        $this->biLetterStats=$this->makeStatFromBiLetters($wordList);

        if(count($wordList)<self::MAX_WORDLIST_SIZE_FOR_SYLLABLE_ANALYSIS) {
            $this->syllablesList=$this->getSyllablesForEachWord($wordList);
            $this->syllablesStats=$this->makeStatFromBiSyllables($this->syllablesList);
        }

    }

    public function getBiLetterStats()
    {
        return $this->biLetterStats;
    }

    public function generateWordsFromUniLetters(int $number, int $length) : array
    {
        $words=[];

        //$availableCharacters=$this->letterList;
        //$availableCharactersNumbers=strlen($availableCharacters);

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

    public function generateWordsFromBiLetters(int $number, int $length, bool $withoutSpace=true)
    {
        $words=[];

        //add 1 char since we want to add a space at the end
        $length++;

        for ($i=0;$i<$number;$i++) {
            $word="";
            $generativeCharacter=" ";
            for ($j=0;$j<$length;$j++) {
                $numberOfPossibilities=$this->biLetterStats[$generativeCharacter]['sum'];
                $letterIndex=rand(0, $numberOfPossibilities - 1);
                $cumulative=0;
                foreach ($this->biLetterStats[$generativeCharacter] as $letter=> $letterOccurences) {
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

    public function generateWordsFromTriLetters(int $number, int $length, bool $withoutSpace=true)
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

            $triLetterStats=$this->makeStatFromTriLetters($this->wordList);
            $generativeCharacters=" ".$word;
            for ($j=0;$j<$length-1;$j++) {
                $numberOfPossibilities=$triLetterStats[$generativeCharacters]['sum'];
                $letterIndex=rand(0, $numberOfPossibilities - 1);
                $cumulative=0;
                foreach ($triLetterStats[$generativeCharacters] as $letter=> $letterOccurences) {
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

    public function generateWordsFromBiSyllables(int $number, int $length, bool $withoutSpace=true)
    {
        //in case syllable statistics are not available
        if(count($this->wordList)>self::MAX_WORDLIST_SIZE_FOR_SYLLABLE_ANALYSIS) {
            return generateWordsFromBiLetters($number, $length, $withoutSpace);
        }

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
        $biLetterList=$this->getBiLetterList($words);
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

    private function getBiLetterList(array $words) : array
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
    private function getSyllablesForEachWord($words) :array {
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