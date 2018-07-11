<?php

/**
 *
 */
namespace util
{
    /**
     * Class character
     * @package util
     */
    class character
    {
        /**
         * @param string $haystack
         * @param string $needle
         *
         * @return bool
         */
        public static function startsWith($haystack, $needle) {
            // search backwards starting from haystack length characters from the end
            return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
        }

        /**
         * @param string $haystack
         * @param string $needle
         *
         * @return bool
         */
        public static function endsWith($haystack, $needle) {
            // search forward starting from end minus needle length characters
            return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
        }

        /**
         * @param string $string
         * @param string $chars
         * @param bool $addpoints
         * @param bool $strict
         *
         * @return bool|string
         */
        public static function createSubString($string, $chars, $addpoints = false, $strict = false)
        {
            $searchLimit = array(" ", "-");
            $string = strip_tags($string);
            if ((strlen($string)) > $chars) {
                foreach ($searchLimit as $searchLimitItem) {
                    $i = strpos($string, $searchLimitItem, ($chars - 10));
                    if ($i !== false) {
                        break;
                    }
                }
                if ($i !== false) {
                    $substr = substr($string, 0, $i);
                    if ($addpoints) {
                        $substr = $substr . "...";
                    }
                    if ($strict) {
                        if (strlen($substr) > $chars) {
                            $substr = substr($substr, 0, ($chars - 4));
                            $substr = $substr . "...";
                        }
                    }
                    return $substr;
                } else {
                    return $string; //or just cut the string on the specified chars limit?
                }
            } else {
                return $string;
            }
        }

        /**
         * @param $string
         * @param $limit
         *
         * @return bool|string
         */
        public static function truncateString($string, $limit)
        {
            $result = $string;
            if (strlen($result) > $limit) {
                $result = substr($result, 0, $limit);
                $result .= "...";
            }
            return $result;
        }

        /**
         * @param string $data
         * @param string $encoding
         *
         * @return string
         */
        public static function sanitize($data, $encoding = 'UTF-8')
        {
            return htmlspecialchars($data, ENT_QUOTES | ENT_HTML401, $encoding);
        }

        /**
         * @param int $length
         *
         * @return string
         */
        public static function getRandomString($length = 64)
        {
            $randomBytes = openssl_random_pseudo_bytes($length);
            return base64_encode($randomBytes);
        }


        /**
         * levenshtein eval
         *
         * @param $string1
         * @param $string2
         * @return mixed
         */
        public static function textSimiliarity($string1, $string2)
        {
            //levenshtein is how many operations must be
            //executed to get between two strings.  We'll
            //count each operation as 10% difference
            $levenshtein = levenshtein($string1, $string2);
            $percentLevenshtein = 100 - ($levenshtein * 10);

            $percentSimiliarText = 0; //pass by ref
            similar_text($string1, $string2, $percentSimiliarText);

            return max($percentLevenshtein, $percentSimiliarText);
        }
    }
}
