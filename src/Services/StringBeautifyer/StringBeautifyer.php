<?php

namespace App\Services\StringBeautifyer;

class StringBeautifyer
{

    const MALE_CHAR=['&','#','~','`','\\','@','!','§','£','$',':','<','>','|','°','%','*','+'];
    const FEMALE_CHAR=[];
    const ACCENTS_AND_SPECIAL=[
        'i'=>['î','ï', 'í', 'ì','1'],
        'e'=>['ê', 'é', 'è', 'ë','€'],
        'a'=>['å', 'à', 'á', 'â', 'ã', 'ä', 'æ','@','ª'],
        'o'=>['ò', 'ó', 'ô', 'õ', 'ö', 'œ','0','Ø', 'º','°'],
        'u'=>['ù', 'ú', 'û', 'ü'],
        'y'=>['ý', 'ÿ'],
        'f'=>['ƒ'],
        's'=>['š','$'],
        'z'=>['ž'],
        'c'=>['©','ç'],
        'r'=>['®'],
    ];
    const BRACES=['()','{}','[]','``','--','~~','<>','><','//','\\\\','/\\','\\/','**','%%','&&'];

    public function beautifyWithMaleChar(string $name) : array
    {
        return [];
    }

    public function beautifyWithFemaleChar(string $name) : array
    {
        return [];
    }

    public static function beautifyWithSpecial(string $name, int $number) : array
    {
        $beautified=[];
        $name=strtolower($name);

        for ($i=0; $i < $number; $i++) {
            $accentAndSpecial=self::ACCENTS_AND_SPECIAL;

            do  {
                $targetChar=array_rand($accentAndSpecial);
                unset($accentAndSpecial[$targetChar]);
            } while ((strpos($name, $targetChar) === false) && (count($accentAndSpecial) > 0));

            if (count($accentAndSpecial) > 0) {
                $newChar=self::ACCENTS_AND_SPECIAL[$targetChar][rand(0,count(self::ACCENTS_AND_SPECIAL[$targetChar])-1)];
                $beautified[]=ucwords(str_replace($targetChar,$newChar,$name));
            }
        }

        return $beautified;
    }

    public function beautifyWithBraces(string $name) : array
    {
        return [];
    }
}