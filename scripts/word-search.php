#!/usr/local/bin/php
<?php


$letterCount = 5;
$unique = RegexWords::GRADE_UNIQUE_LETTERS_ONCE + RegexWords::SHOW_ONLY_UNIQUE_WORDS + RegexWords::COUNT_ONLY_UNIQUE_LETTERS;

RegexWords::show(
    $unique,
    [
        1 => ['',  0],
        2 => ['',  0],
        3 => ['',  0],
        4 => ['',  1],
        5 => ['',  0]
    ],
    '',
    'raise',
    $letterCount,
    false);


/* comment this line to use this example
RegexWords::show(
    [
    // position => letters, EQUALS
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
//*/


class RegexWords
{
    const GRADE_UNIQUE_LETTERS_ONCE = 1;
    const SHOW_ONLY_UNIQUE_WORDS = 2;
    const COUNT_ONLY_UNIQUE_LETTERS = 4;

    // if unique, then return only words with all unique letters
    public static function wordsByCommonLetters(array $words, int $unique = 0): array
    {
        $commonLetters = self::mostCommonLetters($words, $unique);
        return self::gradeWords($words, $commonLetters, $unique);
    }

    public static function gradeWords(array $words, array $gradeLetters, int $unique = 0): array
    {
        $graded = [];
        foreach($words as $word) {
            $letters = str_split(strtolower($word));
            // if unique, we will exclude this word from the list
            if($unique & self::SHOW_ONLY_UNIQUE_WORDS && count(array_unique($letters)) < count($letters)) {
                continue;
            }
            $graded[$word] = 0;
            // if unique, we will ignore the words that do not have all unique letters.
            if($unique & self::GRADE_UNIQUE_LETTERS_ONCE && count(array_unique($letters)) < count($letters)) {
                $letters = array_unique($letters);
            }
            foreach($letters as $l) {
                $graded[$word] += ($gradeLetters[$l] ?? 0) * 1;
            }

        }
        arsort($graded);
        return $graded;
    }

    // get count of how many words a letter belongs to
    public static function mostCommonLetters(array $words, int $unique=0): array
    {
        $common = [];
        foreach($words as $word) {
            $letters = str_split(strtolower($word));
            // if unique, we will ignore the words that do not have all unique letters.
            if($unique & self::COUNT_ONLY_UNIQUE_LETTERS) {
                $letters = array_unique($letters);
            }
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

    public static function show(int $unique, array $letterPositions, string $includeLetters, string $excludeLetters, int $letterCount, bool $anySize = false): array
    {
        $found = self::find($letterPositions, $includeLetters, $excludeLetters, $letterCount, $anySize);
        $gradedWords = self::wordsByCommonLetters($found, $unique);
        foreach(array_slice($gradedWords, 0, 20) as $word => $grade) {
            echo "$word - $grade\n";
        }
        return $gradedWords;
    }

    public static function find(array $letterPositions, string $includeLetters, string $excludeLetters, int $letterCount, bool $anySize = false): array
    {
        if($anySize) {
            $data = file_get_contents(__DIR__ . '/../words.txt');
        } else {
            $data = file_get_contents(__DIR__ . '/words-' . $letterCount . '.txt');
        }
        $matches = [];
        $regex = self::build($letterPositions, $includeLetters, $excludeLetters, $letterCount, $anySize);
        echo $regex."\n\n";
        preg_match_all($regex, $data, $matches);
        return !empty($matches[0]) ? $matches[0] : [];
    }

    public static function build(array $letterPositions, string $includeLetters, string $excludeLetters, int $letterCount, bool $anySize = false): string
    {
        $regex = '';
        if($letterPositions) {
            $maxKey = max(...array_keys($letterPositions));
            if (!empty($letterPositions)) {
                for ($l = 1; $l <= $letterCount; $l++) {
                    // =====  EACH LETTER OPTIONS
                    if(!empty($letterPositions[$l][0])) {
                        $regex .= self::positionLetter($letterPositions[$l][0], !!($letterPositions[$l][1] ?? 0) );
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
        }
        if(empty($regex)) {
            $regex = '.*';
        }
        $regex = self::includeLettersAhead($includeLetters) . self::excludeLettersAhead($excludeLetters) . $regex;
        // =====  BUILD REGEX
        return "/^{$regex}$/im";
    }

    public static function includeLettersAhead(string $letters): string
    {
        $letters = str_split($letters);
        $ret = '';
        foreach ($letters as $l) {
            if($l) {
                $ret .= self::includeLetterAhead($l);
            }
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
            if($l) {
                $ret .= self::excludeLetterAhead($l);
            }
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