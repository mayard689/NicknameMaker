<?php
namespace App\Services\NickNameMaker;

use App\Services\Name\Name;
use BitAndBlack\Syllable\Hyphen\Text;
use BitAndBlack\Syllable\Syllable;
use Exception;

class WordMerger
{

    private $syllable;
    private $words;
    private $wordSyllables;

    public function __construct(array $words)
    {
        if (count($words)<2) {
            throw new Exception("This methos needs at least two words.");
        }

        $this->words = $words;

        $this->syllable = new Syllable(
            'fr',
            $_SERVER['DOCUMENT_ROOT'].'../src/Services/Word/languages',
            $_SERVER['DOCUMENT_ROOT'].'../src/Services/Word/cache',
            new Text('-')
        );

        $this->wordSyllables = [];
        foreach ($words as $word) {
            $this->wordSyllables[] = $this->syllable->splitWord($word);
        }
    }

    public function generateWords(int $number)
    {
        $word1=$this->syllable->splitWord($this->words[0]);
        $word2=$this->syllable->splitWord($this->words[1]);
        $word3=$this->syllable->splitWord($this->words[2]);

        $results = $this->generate($word1, $word2, $number/2);
        $results = array_merge($results, $this->generate($word2, $word1, $number/2));

        $results = array_merge($results, $this->generate($word2, $word3, $number/2));
        $results = array_merge($results, $this->generate($word3, $word2, $number/2));

        $results = array_merge($results, $this->generate($word3, $word1, $number/2));
        $results = array_merge($results, $this->generate($word1, $word3, $number/2));

        $results=array_unique($results);

        $nameResults=[];
        foreach ($results as $result) {
            $nameResults[]=new Name($result, "free words");
        }
        return $nameResults;
    }

    private function generate($word1, $word2, $number)
    {
        for ($j=0; $j<$number; $j++) {
            $syllables=[];

            $keptSyllable=rand(1, count($word1));

            for ($i=0; $i<$keptSyllable; $i++) {
                $syllables[]=$word1[$i];
            }

            $keptSyllable=rand(1, count($word2));

            for ($i = $keptSyllable-1; $i >= 0; $i--) {
                $syllableIndex = count($word2) - $i - 1;
                $syllables[]=$word2[$syllableIndex];
            }

            $result[] = implode('',$syllables);
        }

        return $result;
    }
}
