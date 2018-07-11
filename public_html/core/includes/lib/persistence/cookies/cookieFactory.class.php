<?php

/**
 * Factory for creating HTTP Cookies
 ***/
namespace core\persistence
{
    require_once('cookieItem.class.php');

    /**
     * Class cookieFactory
     * @package core\persistence
     */
    class cookieFactory
    {
        /** @const string : factoryItem name */
        const cookieClass = 'core\persistence\cookieFactory\cookie';

        /** @var \core\persistence\cookieFactory\cookie[]  */
        private static $_Cookies = [];

        /** @var bool[]  */
        private static $_lastResults = array();

        /**
         * @param $name
         *
         * @return \core\persistence\cookieFactory\cookie|mixed|null
         */
        public static function NewCookie($name)
        {
            $c = self::findCookie($name);

            if (get_class($c) !== self::cookieClass) {
                $c = new \core\persistence\cookieFactory\cookie($name, \get_called_class());
            }

            self::$_Cookies[$name] = $c;

            return $c;
        }

        /**
         * Sets one or more cookieFactory\cookies using the php setcookie function
         *
         * @param \core\persistence\cookieFactory\cookie | \core\persistence\cookieFactory\cookie[] $cookies : Arrays of cookieFactory\cookies to set
         *
         * @return \bool[]  self::$_lastResults
         ***/
        public static function setCookies($cookies)
        {
            $cookiesToProcess = array();

            if (!is_array($cookies)) {
                if (get_class($cookies) === self::cookieClass) {
                    $cookiesToProcess[$cookies->_name] = $cookies;
                }
            } else {
                $cookiesToProcess = $cookies;
            }

            foreach ($cookiesToProcess as $cookie) {
                self::$_lastResults[$cookie->_name] = \setcookie($cookie->_name, $cookie->_encoded, $cookie->_expire, $cookie->_path, $cookie->_domain);
            }

            return self::$_lastResults;
        }

        /**
         *  Sets one or more cookieFactory\cookies using the php setcookie function
         *
         * @param \core\persistence\cookieFactory\cookie | \core\persistence\cookieFactory\cookie[] $cookies : Arrays of cookieFactory\cookies to set with raw
         *
         * @return \bool[] self::$_lastResults
         */
        public static function setRawCookies($cookies)
        {
            if (!is_array($cookies)) {
                if (get_class($cookies) === self::cookieClass) {
                    $cookies[] = array($cookies);
                }
            }

            foreach ($cookies as $cookie) {
                self::$_lastResults[$cookie->_name] = \setrawcookie($cookie->_name, $cookie->_encoded, $cookie->_expire, $cookie->_path, $cookie->_domain);
            }

            return self::$_lastResults;
        }

        /**
         * Returns cookie Objection if one is known
         *
         * @param string $cookieName
         *
         * @return \core\persistence\cookieFactory\cookie
         */
        public static function findCookie($cookieName)
        {
            if (array_key_exists($cookieName, self::$_Cookies)) {
                return self::$_Cookies[$cookieName];
            }

            return null;
        }

        /**
         * Sets one or more cookieFactory\cookies using the php setcookie function
         *
         * @param string $cookiedNames
         *
         * @return \core\persistence\cookieFactory\cookie[] : returns array of prepared cookies with previous values loaded
         */
        public static function loadCookies($cookiedNames)
        {
            $newCookies = array();
            $cookieNames = array();

            if (!is_array($cookiedNames)) {
                $cookieNames[] = $cookiedNames;
            }

            foreach ($cookieNames as $c) {
                if (isset($_COOKIE[$c])) {
                    header('CookieLoad-Cookie: ' . $c . " found");
                    $cData = json_decode($_COOKIE[$c]);
                    $newC = self::NewCookie($c);
                    $newC->_value = $cData->value ;
                    $newC->_expire = $cData->expire ;
                    $newC->_path = $cData->path ;
                    $newC->_domain = $cData->domain ;

                    self::$_Cookies[$c] = $newC;

                    $newCookies[] = $newC;
                }
            }

            return $newCookies;
        }
    }
}
