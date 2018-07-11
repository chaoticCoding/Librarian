<?php
use \Firebase\JWT\JWT;

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
namespace core\persistence\JWTFactory
{
    /**
     * Class token
     * @package core\persistence\JWTFactory
     */
    class token
    {
        /** @var string  */
        private $_secretType = '';

        /** @var string  */
        private $_secretPrivate = '';

        /** @var string  */
        private $_secretPublic = '';

        /** @var string  */
        private $_encodingMethod = 'HS256';

        /** @var string  */
        private $_issuer = '';

        /** @var string  */
        private $_audience = '';

        /** @var string  */
        private $_subject = '';

        /** @var int  */
        private $_lifespan = 60;

        /** @var int  */
        private $_comeOfAge = 0;

        /** @var array  */
        private $_payload = [];

        /** @var string  */
        private $_tokenID = '';

        /** @var string  */
        private $_tokenIDold = '';

        /** @var string  */
        private $_builtToken = '';

        /** @var array  */
        private $_LastBuild = [
            'iss' => "http://example.org",
            'aud' => "http://example.com",
            'sub' => "http://example.com",
            'iat' => 1356999524,
            'nbf' => 1357000000,
            'exp' => 1356999524,
            'jti' => "DASASDADSASDASSADASDADSAS",
            'payload' => [],
        ];

        /**
         * token constructor.
         *
         * @param $issuer
         * @param $audience
         * @param $subject
         * @param $encodingMethod
         * @param $secretPublic
         * @param $lifespan
         * @param $comeOfAge
         * @param $secretPrivate
         */
        public function __construct($issuer, $audience, $subject, $encodingMethod, $secretPublic, $lifespan, $comeOfAge, $secretPrivate)
        {

            $this->_issuer = $issuer;
            $this->_audience = $audience;
            $this->_subject = $subject;

            $this->_encodingMethod = $encodingMethod;

            $this->_secretPublic = $secretPublic;

            $this->_lifespan = $lifespan;
            $this->_comeOfAge = $comeOfAge;

            $this->_secretPrivate = $secretPrivate;

            $this->nextID();
        }

        /**
         * Sets the lifespan of the token
         *
         * @param int $lifespan - in secconds how long the token will live from its birth, this should be a reasonable span of time for the life of execution.
         ***/
        public function setLifespan($lifespan = 60)
        {
            $this->_lifespan = $lifespan;
        }

        /**
         * Sets the delay between when the token is spawned and when it comes of age
         *
         * @param int $comeOfAge - in secconds how long the token will the token take to come of age, in most cases this will be short. but token will not be valid until that time.
         ***/
        public function setComesofAge($comeOfAge = 0)
        {
            $this->_comeOfAge = $comeOfAge;
        }

        /**
         *
         *
         * @param string[] $payload: for token to become
         ***/
        public function setPayload(array $payload = [])
        {
            $this->_payload = $payload;
        }

        /**
         * @return string[]
         */
        public function getPayload ()
        {
            return $this->_payload;
        }

        /**
         * @return string
         */
        public function getID()
        {
            return $this->getIDFromSession();
        }

        /**
         * @return string
         */
        public function getIDFromSession() {
            if(isset($this->_payload->s)) {
                if(isset($this->_payload->s->i)) {
                    return $this->_payload->s->i;
                }
            }
            return $this->_tokenID;
        }

        /**
         * @return int
         */
        public function getIssuedTime()
        {
            if($this->_LastBuild !== null && isset($this->_LastBuild->iat) ) {
                return (int) $this->_LastBuild->iat;
            }

            return 0;
        }

        /**
         * @return int
         */
        public function getExpireTime()
        {
            if($this->_LastBuild !== null && isset($this->_LastBuild->exp) ) {
                return (int) $this->_LastBuild->exp;
            }
            return 0;
        }

        /**
         * @return string
         */
        public function nextID()
        {
            $this->_tokenIDold = $this->_tokenID;
            $this->_tokenID = \core\unique\UUID::v4();

            return $this->_tokenID;
        }


        /**
         * build the token with out encoding. warning, Will set assemble lifespan and comingofAge into nbf and exp values
         *
         * @return string
         */
        public function build ()
        {
            $this->_tokenID = $this->nextID();

            $this->_LastBuild['iss'] = $this->_issuer;
            $this->_LastBuild['aud'] = $this->_audience;
            $this->_LastBuild['sub'] = $this->_subject;

            $this->_LastBuild['jti'] = $this->_tokenID;

            $this->_LastBuild['iat'] = time();
            $this->_LastBuild['nbf'] = $this->_LastBuild['iat'] + $this->_comeOfAge;
            $this->_LastBuild['exp'] = $this->_LastBuild['iat'] + $this->_lifespan;

            $this->_LastBuild['payload'] = $this->_payload;

            return $this->_tokenID;

        }

        /**
         * TODO - Add support for HS ($key) and RS (OpenSSL private/public cert)
         *
         * @param bool $newBuild
         *
         * @return string
         */
        public function encode($newBuild = true)
        {
            if($newBuild === true) {
                $this->build();
            }

            $this->_builtToken = \Firebase\JWT\JWT::encode($this->_LastBuild, $this->_secretPublic, $this->_encodingMethod);

            return $this->_builtToken;
        }


        /**
         * TODO - Add support for HS ($key) and RS (OpenSSL private/public cert)
         *
         * @param $token
         *
         * @return \core\persistence\JWTFactory\token
         */
        public function decode($token) {
            try {
                $cJWT = \Firebase\JWT\JWT::decode($token, $this->_secretPublic, array($this->_encodingMethod));

                if ($cJWT !== null) {
                    $this->_LastBuild = $cJWT;

                    $this->_payload = $cJWT->payload;

                    return $this;
                }
            } catch (\Throwable $e) { // Invalid token, catch needed for firebase
            } catch (\Exception $e) { // Invalid token, catch needed for firebase
                return null;
            }
            return null;
        }
    }
}
