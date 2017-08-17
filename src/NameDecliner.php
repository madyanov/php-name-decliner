<?php

namespace Madyanov;

class NameDecliner
{
    const CASE_NOMINATIVE = 1; // именительный
    const CASE_GENITIVE = 2; // родительный
    const CASE_DATIVE = 3; // дательный
    const CASE_ACCUSATIVE = 4; // винительный
    const CASE_INSTRUMENTAL = 5; // творительный
    const CASE_PREPOSITIONAL = 6; // предложный

    private $originalWord;
    private $normalizedWord;
    private $splitWord;
    private $wordLength;

    public function __construct($word)
    {
        $this->originalWord = $word;
        $this->normalizedWord = mb_strtolower($word);
        $this->splitWord = $this->splitWord($this->normalizedWord);
        $this->wordLength = mb_strlen($word);
    }

    public function applyMaleNameRules()
    {
        // @see http://www.imena.org/decl_mn.html
        if ($this->equals('павел')) {
            // В именах Лев и Павел при склонении появляются беглые гласные:
            return $this->decline('--ла', '--лу', '--ла', '--лом', '--ле');
        } else if ($this->equals('лев')) {
            return $this->decline('--ьва', '--ьву', '--ьва', '--ьвом', '--ьве');
        } else if ($this->ends('[жчшщц]')) {
            // Если имя оканчивается на шипящий или на ц, в творительном падеже пишется -ем, а не -ом:
            return $this->decline('а', 'у', 'а', 'ем', 'е');
        } else if ($this->ends('й')) {
            if ($this->ends('[аеёоуяю]-')) {
                // Мужские имена, оканчивающиеся на -ай, -ей, -ой, -уй, -яй, -юй, склоняются следующим образом:
                return $this->decline('-я', '-ю', '-я', '-ем', '-е');
            } else {
                // Мужские имена, оканчивающиеся на -ий, в предложном падеже оканчиваются на -и:
                return $this->decline('-я', '-ю', '-я', '-ем', '-и');
            }
        } else if ($this->ends('[бвгджзлмнрйпфктшсхцчщ]')) {
            // Мужские имена, оканчивающиеся на твердые согласные, склоняются следующим образом:
            return $this->decline('а', 'у', 'а', 'ом', 'е');
        } else if ($this->ends('[бвгджзлмнрйпфктшсхцчщ][ь]')) {
            // Мужские имена, оканчивающиеся на мягкие согласные, склоняются следующим образом:
            return $this->decline('-я', '-ю', '-я', '-ем', '-е');
        } else if ($this->ends('а')) {
            if ($this->ends('![жчшщгкхц]-')) {
                // Мужские имена, оканчивающиеся на -а не после шипящих и не после г, к, х, ц, склоняются следующим образом:
                return $this->decline('-ы', '-е', '-у', '-ой', '-е');
            } else {
                // После шипящих и после г, к, х в родительном падеже пишется -и, а не -ы:
                return $this->decline('-и', '-е', '-у', '-ей', '-е'); // TODO: Если за шипящим следует безударное -а, в творительном падеже пишется -ей, а не -ой
            }
        } else if ($this->ends('я')) {
            if ($this->ends('и-') && $this->syllables() !== 2) {
                // Мужские имена, оканчивающиеся на безударное -я, которому предшествует -и- (за исключением двусложных),
                // имеют в родительном, дательном и предложном падежах окончание -и:
                return $this->decline('-и', '-и', '-ю', '-ей', '-и');
            } else {
                // Мужские имена, оканчивающиеся на -я (кроме более длинных, чем двусложные, имен, оканчивающихся на -ия с безударным -я),
                // склоняются по следующим образцам:
                return $this->decline('-и', '-е', '-ю', '-ей', '-е');
            }
        }

        return $this->filledResult();
    }

    public function applyFemaleNameRules()
    {
        // @see http://www.imena.org/decl_fn.html
        if ($this->ends('а')) {
            // Женские имена, оканчивающиеся на -а не после шипящих и не после ц, г, к, х, склоняются следующим образом:
            if ($this->ends('![гкхцжчшщ]-')) {
                return $this->decline('-ы', '-е', '-у', '-ой', '-е');
            } else if ($this->ends('[гкхцжчшщ]-')) {
                // После шипящих и после г, к, х в родительном падеже пишется и, а не ы:
                return $this->decline('-и', '-е', '-у', '-ой', '-е');
            } else {
                // Если за шипящим следует безударное -а, в творительном падеже пишется -ей, а если ударное —то -ой:
                return $this->decline('-ы', '-е', '-у', '-ей', '-е');
            }
        } else if ($this->ends('я')) {
            if ($this->ends('и-')) {
                if ($this->syllables() === 2) {
                    // Двусложные имена, оканчивающиеся на -ия (с безударным -я), могут склоняться по следующим правилам:
                    return $this->decline('-и', '-е', '-ю', '-ей', '-е');
                } else {
                    // Женские имена, оканчивающиеся на безударное -я, которому предшествует -и-,
                    // кроме двусложных типа Ия, Лия, Вия, Бия, имеют в родительном, дательном и предложном падежах окончание -и:
                    return $this->decline('-и', '-и', '-ю', '-ей', '-и');
                }
            } else {
                // Женские имена, оканчивающиеся на -я (кроме более длинных, чем двусложные,
                // оканчивающихся на сочетание -и- с безударным -я), склоняются следующим образом:
                return $this->decline('-и', '-е', '-ю', '-ей', '-е');
            }
        } else if ($this->ends('[бвгджзлмнрйпфктшсхцчщ][яёюеиь]')) {
            // Женские имена, оканчивающиеся на мягкие согласные, склоняются следующим образом:
            return $this->decline('-и', '-и', '', '-ью', '-и');
        } else if ($this->ends('[жчшщ]')) {
            // По этому же типу могут склоняться женские имена, оканчивающиеся на шипящие:
            return $this->decline('и', 'и', '', 'ью', 'и');
        }
        
        return $this->filledResult();
    }

    public function applyMaleLastNameRules()
    {
        if ($this->ends('[ое]в') || $this->ends('ин')) {
            return $this->decline('а', 'у', 'а', 'ым', 'е');
        } else if ($this->ends('ой')) {
            return $this->decline('--ого', '--ому', '--ого', '--ым', '--ом');
        } else if ($this->ends('ый')) {
            return $this->decline('--ого', '--ому', '--ого', '--ым', '--ом');
        } else if ($this->ends('ий')) {
            return $this->decline('--ого', '--ому', '--ого', '--им', '--ом');
        } else if (!$this->ends('[иы]х') && $this->ends('[бвгджзлмнрпфктшсхцчщ]')) {
            return $this->decline('а', 'у', 'а', 'ом', 'е');
        } else if ($this->ends('[йь]')) {
            return $this->decline('-я', '-ю', '-я', '-ем', '-е');
        }

        return $this->filledResult();
    }

    public function applyFemaleLastNameRules()
    {
        if ($this->ends('[ое]ва') || $this->ends('ина')) {
            return $this->decline('-ой', '-ой', '-у', '-ой', '-ой');
        } else if ($this->ends('ая')) {
            // TODO: Не уверен насчет шипящих
            return $this->decline('--ой', '--ой', '--ую', '--ой', '--ой');
        }
        
        return $this->filledResult();
    }

    private function equals($word)
    {
        return $this->normalizedWord === mb_strtolower($word);
    }

    private function ends($pattern)
    {
        $pattern = mb_strtolower($pattern);
        $patternLength = mb_strlen($pattern);

        if (mb_substr($this->normalizedWord, -$patternLength) === $pattern) {
            return true;
        }

        $word = $this->splitWord;
        $pattern = $this->splitWord($pattern);

        $inFold = array();
        $wordLastIndex = mb_strlen($this->normalizedWord) - 1;

        $fsmState = 0;
        $j = 0;

        for ($i = $patternLength - 1; $i >= 0; $i--) {
            $char = $pattern[$i];

            if ($fsmState === 0) {
                if ($char === ']') {
                    $fsmState = 1;
                } else if ($char !== '!') {
                    if ($char !== '-') {
                        $index = $wordLastIndex - $j;

                        if (!isset($word[$index])) {
                            return false;
                        }

                        if (isset($pattern[$i - 1]) && $pattern[$i - 1] === '!') {
                            if ($char === $word[$index]) {
                                return false;
                            }
                        } else if ($char !== $word[$index]) {
                            return false;
                        }
                    }

                    $j++;
                }
            } else if ($fsmState === 1) {
                if ($char === '[') {
                    $index = $wordLastIndex - $j;

                    if (!isset($word[$index])) {
                        return false;
                    }

                    if (isset($pattern[$i - 1]) && $pattern[$i - 1] === '!') {
                        if (in_array($word[$index], $inFold, true)) {
                            return false;
                        }
                    } else if (!in_array($word[$index], $inFold, true)) {
                        return false;
                    }

                    $fsmState = 0;
                    $inFold = array();
                    $j++;
                } else if ($char !== '!') {
                    $inFold[] = $char;
                }
            }
        }

        return true;
    }

    private function decline($gen, $dat, $acc, $ins, $pre)
    {
        $result = array();
        $length = $this->wordLength;

        foreach (array(
            self::CASE_GENITIVE => $gen,
            self::CASE_DATIVE => $dat,
            self::CASE_ACCUSATIVE => $acc,
            self::CASE_INSTRUMENTAL => $ins,
            self::CASE_PREPOSITIONAL => $pre,
        ) as $case => $suffix) {
            $word = $this->originalWord;
            $start = mb_strrpos($suffix, '-');

            if ($start !== false) {
                $suffix = mb_substr($suffix, $start + 1);
                $word = mb_substr($word, 0, $length - $start - 1);
            }

            $result[$case] = $word . $suffix;
        }

        return $result;
    }

    private function syllables()
    {
        return $this->wordLength - mb_strlen(
            str_replace(array('а', 'о', 'у', 'э', 'ы', 'я', 'ё', 'ю', 'е', 'и'), '', $this->normalizedWord)
        );
    }

    private function splitWord(string $word)
    {
        return preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY);
    }

    private function filledResult()
    {
        return array(
            self::CASE_GENITIVE => $this->originalWord,
            self::CASE_DATIVE => $this->originalWord,
            self::CASE_ACCUSATIVE => $this->originalWord,
            self::CASE_INSTRUMENTAL => $this->originalWord,
            self::CASE_PREPOSITIONAL => $this->originalWord,
        );
    }
}