#!/usr/local/bin/php
<?php

RegexWords::show(
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
    22,
true);

class RegexWords
{
    public static function show(array $letterPositions, string $includeLetters, string $excludeLetters, int $letterCount, bool $anySize = false)
    {
        $found = RegexWords::find($letterPositions, $includeLetters, $excludeLetters, $letterCount, $anySize);
        echo implode("\n", $found) . "\n";
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
        echo $regex;
        return !empty($matches[0]) ? $matches[0] : [];
    }

    public static function build(array $letterPositions, string $includeLetters, string $excludeLetters, int $letterCount, bool $anySize = false): string
    {
        $regex = '';
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
                if($anySize && $l > $maxKey) {
                    $regex .= '?';
                }
            }
        }
        $regex .= '';
        // =====  INCLUDES LETTERS
        $regex .= RegexWords::includeLetters($includeLetters, $letterCount);
        // =====  EXCLUDE LETTERS
        $regex .= RegexWords::excludeLetters($excludeLetters, $letterCount);
        // =====  BUILD REGEX
        return "/^{$regex}$/im";
    }

    public static function includeLetters(string $letters, int $letterCount): string
    {
        $letters = str_split($letters);
        $ret = '';
        foreach ($letters as $l) {
            $ret .= self::includeLetter($l, $letterCount);
        }
        return $ret;
    }

    public static function includeLetter(string $letter, int $letterCount): string
    {
        return '(?<![^' . $letter . ']{' . $letterCount . '})';
    }

    public static function excludeLetters(string $letters, int $letterCount): string
    {
        $letters = str_split($letters);
        $ret = '';
        foreach ($letters as $l) {
            $ret .= self::excludeLetter($l, $letterCount);
        }
        return $ret;
    }

    public static function excludeLetter(string $letter, int $letterCount): string
    {
        return '(?<=[^' . $letter . ']{' . $letterCount . '})';
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