<?php

/**
 * CookieFactory builder and decoding JWT tokens
 *
 * iss (issuer) identifies principal that issued the JWT;
 * aud (audience) The "aud" (audience) claim identifies the recipients that the JWT is intended for.
 * sub (subject) identifies the subject of the JWT;
 * iat (Issued at) The "iat" (issued at) claim identifies the time at which the JWT was issued.
 * nbf (Not before ) the not-before time claim identifies the time on which the JWT will start to be accepted for processing.
 * exp (expiration time) The "exp" (expiration time) claim identifies the expiration time on or after which the JWT MUST NOT be accepted for processing.
 * jti (JWT ID) case sensitive unique identifier of the token even among different issuers.
 ***/
namespace core\persistence
{
    require_once 'JWT.class.php';

    /**
     * Class JWTFactory
     * @package core\persistence
     */
    class JWTFactory
    {
        /** @var string  */
        private static $_encodingMethod = "";

        /** @var string  */
        private static $_secretPublic = "";

        /** @var string  */
        private static $_secretType = "";

        /** @var string  */
        private static $_secretPrivate = "";

        /** @var string  */
        private static $_issuer = "";

        /** @var string  */
        private static $_audience = "";

        /** @var string  */
        private static $_subject = "";

        /** @var int  */
        private static $_comeOfAge = 0;

        /** @var int  */
        private static $_lifespan = 60;

        /** @var \core\persistence\JWTFactory\token[] */
        private static $_tokens = array();


        /**
         * Sets the default information needed for building JWT Tokens should be setup with a pass from a enviroment setting
         *
         * @param string $issuer
         * @param string $audience
         * @param string $subject
         * @param string $encodingMethod
         * @param string $secretPublic
         * @param int    $lifespan
         * @param int    $comeOfAge
         * @param string $secretPrivate
         */
        public static function init($issuer, $audience, $subject, $encodingMethod, $secretPublic, $lifespan = 60, $comeOfAge = 0, $secretPrivate = "")
        {
            self::$_encodingMethod = $encodingMethod;

            self::$_secretPublic = $secretPublic;
            self::$_secretPrivate = $secretPrivate;

            self::$_issuer = $issuer;
            self::$_audience = $audience;
            self::$_subject = $subject;

            self::$_lifespan = $lifespan;
            self::$_comeOfAge = $comeOfAge;
        }


        /**
         * @return \core\persistence\JWTFactory\token : token from the facory containing decoded values assuming a valid token or null if the token is invalid
         */
        public static function newToken()
        {
            $jwt = new \core\persistence\JWTFactory\token(self::$_issuer, self::$_audience, self::$_subject, self::$_encodingMethod, self::$_secretPublic, self::$_lifespan, self::$_comeOfAge, self::$_secretPrivate);

            $cJWT_ID = $jwt->getID();

            self::$_tokens[$cJWT_ID] = $jwt;

            return $jwt;
        }

        /**
         *
         *
         * @param string $jti : JWT encoded as string
         *
         * @return \core\persistence\JWTFactory\token : token from the facory containing decoded values assuming a valid token or null if the token is unvalid
         ***/
        public static function findToken($JWT_ID)
        {
            if(in_array($JWT_ID, self::$_tokens,false)) {
                if(get_class(self::$_tokens[$JWT_ID]) === "core\persistence\JWTFactory\token") {
                    return self::$_tokens[$JWT_ID];
                }
            }

            return null;
        }

        /**
         *
         *
         * @param string $token : JWT encoded as string
         *
         * @return \core\persistence\JWTFactory\token token from the factory containing decoded values assuming a valid token or null if the token is invalid
         ***/
        public static function unpackToken($token)
        {
            try {
                $jwt = new \core\persistence\JWTFactory\token(self::$_issuer, self::$_audience, self::$_subject, self::$_encodingMethod, self::$_secretPublic, self::$_lifespan, self::$_comeOfAge, self::$_secretPrivate);

                if($jwt->decode($token)) {
                    $cJWT_ID = $jwt->getID();

                    self::$_tokens[$cJWT_ID] = $jwt;
                    return $jwt;
                }
            } catch (\Throwable $e) { // Invalid token, catch needed for firebase
            } catch (\Exception $e) { // Invalid token, catch needed for firebase
            }
        }
    }
}
