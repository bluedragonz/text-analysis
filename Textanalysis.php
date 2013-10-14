<?php

class Textanalysis {

        protected $strEncoding = '';

        public function __construct($strEncoding = '') {
            if ($strEncoding <> '') {

                $this->strEncoding = $strEncoding;
            }
        }

        public function flesch_kincaid_reading_ease($text) {
            $text = $this->clean_text($text);
            return round((206.835 - (1.015 * $this->average_words_per_sentence($text)) - (84.6 * $this->average_syllables_per_word($text))), 1);
        }

        public function flesch_kincaid_grade_level($text) {
            $text = $this->clean_text($text);
            return round(((0.39 * $this->average_words_per_sentence($text)) + (11.8 * $this->average_syllables_per_word($text)) - 15.59), 1);
        }

        public function gunning_fog_score($text) {
            $text = $this->clean_text($text);
            return round((($this->average_words_per_sentence($text) + $this->percentage_words_with_three_syllables($text, false)) * 0.4), 1);
        }

        public function coleman_liau_index($text) {
            $text = $this->clean_text($text);
            return round( ( (5.89 * ($this->letter_count($text) / $this->word_count($text))) - (0.3 * ($this->sentence_count($text) / $this->word_count($text))) - 15.8 ), 1);
        }

        public function smog_index($text) {
            $text = $this->clean_text($text);
            return round(1.043 * sqrt(($this->words_with_three_syllables($text) * (30 / $this->sentence_count($text))) + 3.1291), 1);
        }

        public function automated_readability_index($text) {
            $text = $this->clean_text($text);
            return round(((4.71 * ($this->letter_count($text) / $this->word_count($text))) + (0.5 * ($this->word_count($text) / $this->sentence_count($text))) - 21.43), 1);
        }

        public function text_length($text) {
            $intTextLength = 0;
            try {
                if ($this->strEncoding == '') {
                    $intTextLength = mb_strlen($text);
                } else {
                    $intTextLength = mb_strlen($text, $this->strEncoding);
                }
            } catch (Exception $e) {
                $intTextLength = strlen($text);
            }
            return $intTextLength;
        }

        public function letter_count($text) {
            $text = $this->clean_text($text);
            $intTextLength = 0;
            $text = preg_replace('/[^A-Za-z]+/', '', $text);
            try {
                if ($this->strEncoding == '') {
                    $intTextLength = mb_strlen($text);
                } else {
                    $intTextLength = mb_strlen($text, $this->strEncoding);
                }
            } catch (Exception $e) {
                $intTextLength = strlen($text);
            }
            return $intTextLength;
        }

        protected function clean_text($text) {

            $fullStopTags = array('li', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'dd');
            foreach ($fullStopTags as $tag) {
                $text = str_ireplace('</'.$tag.'>', '.', $text);
            }
            $text = strip_tags($text);
            $text = preg_replace('/[",:;()-]/', ' ', $text);
            $text = preg_replace('/[\.!?]/', '.', $text);
            $text = trim($text) . '.';
            $text = preg_replace('/[ ]*(\n|\r\n|\r)[ ]*/', ' ', $text);
            $text = preg_replace('/([\.])[\. ]+/', '$1', $text);
            $text = trim(preg_replace('/[ ]*([\.])/', '$1 ', $text));
            $text = preg_replace('/ [0-9]+ /', ' ', ' ' . $text . ' ');
            $text = preg_replace('/[ ]+/', ' ', $text);
            $text = preg_replace_callback('/\. [^ ]+/', create_function('$matches', 'return strtolower($matches[0]);'), $text);
            return trim($text);
        }

        protected function lower_case($text) {
            $strLowerCaseText = '';
            try {
                if ($this->strEncoding == '') {
                    $strLowerCaseText = mb_strtolower($text);
                } else {
                    $strLowerCaseText = mb_strtolower($text, $this->strEncoding);
                }
            } catch (Exception $e) {
                $strLowerCaseText = strtolower($text);
            }
            return $strLowerCaseText;
        }

        protected function upper_case($text) {
            $strUpperCaseText = '';
            try {
                if ($this->strEncoding == '') {
                    $strUpperCaseText = mb_strtoupper($text);
                } else {
                    $strUpperCaseText = mb_strtoupper($text, $this->strEncoding);
                }
            } catch (Exception $e) {
                $strUpperCaseText = strtoupper($text);
            }
            return $strUpperCaseText;
        }

        protected function substring($text, $intStart, $intLength) {
            $strSubstring = '';
            try {
                if ($this->strEncoding == '') {
                    $strSubstring = mb_substr($text, $intStart, $intLength);
                } else {
                    $strSubstring = mb_substr($text, $intStart, $intLength, $this->strEncoding);
                }
            } catch (Exception $e) {
                $strSubstring = substr($text, $intStart, $intLength);
            }
            return $strSubstring;
        }

        public function sentence_count($text) {
            $text = $this->clean_text($text);
            $intSentences = max(1, $this->text_length(preg_replace('/[^\.!?]/', '', $text)));
            return $intSentences;
        }

        public function word_count($text) {
            $text = $this->clean_text($text);
            $intWords = 1 + $this->text_length(preg_replace('/[^ ]/', '', $text));
            return $intWords;
        }

        public function average_words_per_sentence($text) {
            $text = $this->clean_text($text);
            $intSentenceCount = $this->sentence_count($text);
            $intWordCount = $this->word_count($text);
            return ($intWordCount / $intSentenceCount);
        }

        public function total_syllables($text) {
            $text = $this->clean_text($text);
            $intSyllableCount = 0;
            $arrWords = explode(' ', $text);
            for ($i = 0, $intWordCount = count($arrWords); $i < $intWordCount; $i++) {
                $intSyllableCount += $this->syllable_count($arrWords[$i]);
            }
            return $intSyllableCount;
        }

        public function average_syllables_per_word($text) {
            $text = $this->clean_text($text);
            $intSyllableCount = 0;
            $intWordCount = $this->word_count($text);
            $arrWords = explode(' ', $text);
            for ($i = 0; $i < $intWordCount; $i++) {
                $intSyllableCount += $this->syllable_count($arrWords[$i]);
            }
            return ($intSyllableCount / $intWordCount);
        }

        public function words_with_three_syllables($text, $blnCountProperNouns = true) {
            $text = $this->clean_text($text);
            $intLongWordCount = 0;
            $intWordCount = $this->word_count($text);
            $arrWords = explode(' ', $text);
            for ($i = 0; $i < $intWordCount; $i++) {
                if ($this->syllable_count($arrWords[$i]) > 2) {
                    if ($blnCountProperNouns) {
                        $intLongWordCount++;
                    } else {
                        $strFirstLetter = $this->substring($arrWords[$i], 0, 1);
                        if ($strFirstLetter !== $this->upper_case($strFirstLetter)) {
                            // First letter is lower case. Count it.
                            $intLongWordCount++;
                        }
                    }
                }
            }
            return ($intLongWordCount);
        }

        public function percentage_words_with_three_syllables($text, $blnCountProperNouns = true) {
            $text = $this->clean_text($text);
            $intWordCount = $this->word_count($text);
            $intLongWordCount = $this->words_with_three_syllables($text, $blnCountProperNouns);
            $intPercentage = (($intLongWordCount / $intWordCount) * 100);
            return ($intPercentage);
        }

        public function syllable_count($word) {

            $word = preg_replace('/[^A_Za-z]/' , '', $word);

            $intSyllableCount = 0;
            $word = $this->lower_case($word);

            $arrProblemWords = Array(
                 'simile' => 3
                ,'forever' => 3
                ,'shoreline' => 2
            );
            if (isset($arrProblemWords[$word])) {
                return $arrProblemWords[$word];
            }

            $arrSubSyllables = Array(
                 'cial'
                ,'tia'
                ,'cius'
                ,'cious'
                ,'giu'
                ,'ion'
                ,'iou'
                ,'sia$'
                ,'[^aeiuoyt]{2,}ed$'
                ,'.ely$'
                ,'[cg]h?e[rsd]?$'
                ,'rved?$'
                ,'[aeiouy][dt]es?$'
                ,'[aeiouy][^aeiouydt]e[rsd]?$'
                ,'[aeiouy]rse$'
            );

            $arrAddSyllables = Array(
                 'ia'
                ,'riet'
                ,'dien'
                ,'iu'
                ,'io'
                ,'ii'
                ,'[aeiouym]bl$'
                ,'[aeiou]{3}'
                ,'^mc'
                ,'ism$'
                ,'([^aeiouy])\1l$'
                ,'[^l]lien'
                ,'^coa[dglx].'
                ,'[^gq]ua[^auieo]'
                ,'dnt$'
                ,'uity$'
                ,'ie(r|st)$'
            );

            $arrPrefixSuffix = Array(
                 '/^un/'
                ,'/^fore/'
                ,'/ly$/'
                ,'/less$/'
                ,'/ful$/'
                ,'/ers?$/'
                ,'/ings?$/'
            );

            $word = preg_replace($arrPrefixSuffix, '', $word, -1, $intPrefixSuffixCount);

            $word = preg_replace('/[^a-z]/is', '', $word);
            $arrWordParts = preg_split('/[^aeiouy]+/', $word);
            $intWordPartCount = 0;
            foreach ($arrWordParts as $wordPart) {
                if ($wordPart <> '') {
                    $intWordPartCount++;
                }
            }

            $intSyllableCount = $intWordPartCount + $intPrefixSuffixCount;
            foreach ($arrSubSyllables as $strSyllable) {
                $intSyllableCount -= preg_match('/' . $strSyllable . '/', $word);
            }
            foreach ($arrAddSyllables as $strSyllable) {
                $intSyllableCount += preg_match('/' . $strSyllable . '/', $word);
            }
            $intSyllableCount = ($intSyllableCount == 0) ? 1 : $intSyllableCount;
            return $intSyllableCount;
        }

    }

?>
