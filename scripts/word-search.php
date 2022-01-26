#!/usr/local/bin/php
<?php

$letterCount = 5;
$found = RegexWords::show(
    [
/* position => letters, EQUALS  */
          1 => ['s',    1],
          2 => ['arw',  0],
          3 => ['rae',  0],
          4 => ['a',    1],
          5 => ['r',    1]
    ],
    'sar',
    'botcewiz',
    $letterCount,
false);

$common = RegexWords::mostCommonLetters($found);
$commonCount = array_slice($common,0, $letterCount);
var_dump($commonCount);

class RegexWords
{
    public static function mostCommonLetters(array $words): array
    {
        $common = [];
        foreach($words as $word) {
            $letters = str_split(strtolower($word));
            foreach($letters as $l) {
                if (empty($common[$l])) {
                    $common[$l] = 1;
                } else {
                    $common[$l]++;
                }
            }
        }
        arsort($common);
        return $common;
    }

    public static function show(array $letterPositions, string $includeLetters, string $excludeLetters, int $letterCount, bool $anySize = false)
    {
        $found = RegexWords::find($letterPositions, $includeLetters, $excludeLetters, $letterCount, $anySize);
        echo implode("\n", $found) . "\n";
        return $found;
    }

    public static function find(array $letterPositions, string $includeLetters, string $excludeLetters, int $letterCount, bool $anySize = false): array
    {
        if($anySize) {
            $data = file_get_contents(__DIR__ . '/../words.txt');
        } else {
            $data = file_get_contents(__DIR__ . '/words-' . $letterCount . '.txt');
        }
        $matches = [];
        $regex = RegexWords::build($letterPositions, $includeLetters, $excludeLetters, $letterCount, $anySize);
        preg_match_all($regex, $data, $matches);
        echo $regex."\n\n";
        return !empty($matches[0]) ? $matches[0] : [];
    }

    public static function build(array $letterPositions, string $includeLetters, string $excludeLetters, int $letterCount, bool $anySize = false): string
    {
        $regex = RegexWords::includeLettersAhead($includeLetters);
        $regex .= RegexWords::excludeLettersAhead($excludeLetters);
        $maxKey = max(...array_keys($letterPositions));
        if (!empty($letterPositions)) {
            for ($l = 1; $l <= $letterCount; $l++) {
                // =====  EACH LETTER OPTIONS
                if(!empty($letterPositions[$l][0])) {
                    $regex .= RegexWords::positionLetter($letterPositions[$l][0], !!($letterPositions[$l][1] ?? 0) );
                } else {
                    $regex .= '.';
                }
                // make anything after the last $letterPositions optional
                if($anySize) {
                    // limit the requirement of extra letters
                    if($l > $maxKey){
                        $regex .= '?';
                    }
                }
            }
        }
        $regex .= '';
        // =====  BUILD REGEX
        return "/^{$regex}$/im";
    }

    public static function includeLettersAhead(string $letters): string
    {
        $letters = str_split($letters);
        $ret = '';
        foreach ($letters as $l) {
            $ret .= self::includeLetterAhead($l);
        }
        return $ret;
    }

    public static function includeLetterAhead(string $letter): string
    {
        return '(?=.*[' . $letter . ']+.*)';
    }

    public static function excludeLettersAhead(string $letters): string
    {
        $letters = str_split($letters);
        $ret = '';
        foreach ($letters as $l) {
            $ret .= self::excludeLetterAhead($l);
        }
        return $ret;
    }

    public static function excludeLetterAhead(string $letter): string
    {
        return '(?!.*[' . $letter . ']+.*)';
    }

    public static function includeLettersBehind(string $letters, ?int $letterCount = null): string
    {
        $letters = str_split($letters);
        $ret = '';
        foreach ($letters as $l) {
            $ret .= self::includeLetterBehind($l, $letterCount);
        }
        return $ret;
    }

    public static function includeLetterBehind(string $letter, ?int $letterCount = null): string
    {
        $cnt = $letterCount ? '{' . $letterCount . '}' : '';
        return '(?<![^' . $letter . ']' . $cnt . ')';
    }

    public static function excludeLettersBehind(string $letters, ?int $letterCount = null): string
    {
        $letters = str_split($letters);
        $ret = '';
        foreach ($letters as $l) {
            $ret .= self::excludeLetterBehind($l, $letterCount);
        }
        return $ret;
    }

    public static function excludeLetterBehind(string $letter, ?int $letterCount = null): string
    {
        $cnt = $letterCount ? '{' . $letterCount . '}' : '';
        return '(?<=[^' . $letter . ']' . $cnt . ')';
    }

    public static function positionLetter(string $string, bool $equals = false): string
    {
        if (empty($string)) {
            return '.';
        }
        if ($equals) {
            return $string;
        }
        return '[^' . $string . ']';
    }
}