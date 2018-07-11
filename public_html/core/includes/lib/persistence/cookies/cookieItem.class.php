<?php

/**
 * Cookie Container for retaining local data related to created coodies
 ***/
namespace core\persistence\cookieFactory {

    /**
     * Class cookie
     * @package core\persistence\cookieFactory
     */
    class cookie
    {

        /** @var   */
        public $_name;
        /** @var   */
        public $_value;
        /** @var int  */
        public $_expire = 0;
        /** @var string  */
        public $_path   = "/";
        /** @var string  */
        public $_domain = "";

        /** @var string  */
        public $_encoded = "";

        /** @var bool  */
        public $_writeAsRaw = false;

        /** @var \core\persistence\cookieFactory : Constructor reference for controller who knows what todo with me  */
        private $_myFactory;

        /**
         * Local construction always requires name for factory assignment & $myFactory
         *
         * @param string $name : name of cookie and factory key
         * @param \core\persistence\cookieFactory $myFactory : factory for creation
         * @param int $expire : 86400
         ***/
        public function __construct($name,  $myFactory, $expire = 86400)
        {
            $this->_name = $name;
            $this->_myFactory = $myFactory;

            $this->setLifespan( $expire );
        }

        /**
         * @param \int $timestamp
         */
        public function setexpire($timestamp) {

            $this->_expire = $timestamp;

        }

        /**
         *  uses factory to write the cookie back,
         *
         * @param int $expire
         */
        public function setLifespan($expire = 86400) {

            $this->setexpire( time() + $expire);

        }

        /**
         * uses factory to write the cookie back,
         *
         * @return bool[]
         */
        public function set()
        {
            $callTo =  $this->_myFactory;

            $a = [
                'name' => $this->_name,
                'value' => $this->_value,
                'expire' => $this->_expire,
                'path' => $this->_path,
                'domain' => $this->_domain
            ];

            $this->_encoded = json_encode($a);

            if ($this->_writeAsRaw == true) {
                return $callTo->setRawCookies($this);
            }

            return $callTo::setCookies($this);
        }

        /**
         * Purge Data for existing cookie and imidiately write.
         ***/
        public function purge()
        {
            $this->_value = "";
            $this->_expire = -1;

            $this->set();
        }
    }
}
