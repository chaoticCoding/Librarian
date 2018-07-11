<?php

namespace core\datasource\memcache
{
    class DAO
    {
        /** @var \Memcache */
        private $_memcacheInstance;

        /** @var  bool : should memcache endpoint use compression */
        private $_useCompression;

        /** @var string[] : $host => $port */
        private $_hosts = [];


        /**
         * DAO constructor.
         */
        public function __construct ($useCompression = 0)
        {
            $this->_useCompression = $useCompression;

            $this->_memcacheInstance =  new \Memcache();
        }

        /**
         * @param $host
         * @param $port
         */
        public function addHost($host, $port)
        {
            $this->_hosts[$host] = $port;

            $this->_memcacheInstance->addServer($host, $port);
        }

        /**
         * @return array|bool
         */
        public function getExtendedStats()
        {
            $stats = $this->_memcacheInstance->getExtendedStats();

            return $stats;
        }

        /**
         * @return bool
         */
        public function checkStatus()
        {
            $stats = $this->getExtendedStats();

            foreach ($this->_hosts as $host => $port) {
                if($stats["$host:$port"] !== FALSE) {

                    return true;
                }
            }

            return FALSE;
        }

        /**
         * Adds or Updates key pair in cache
         *
         * @param string $key
         * @param string $value
         * @param int    $expire : in seconds
         *
         * @return bool
         *
         * @throws \Exception
         */
        public function set($key, $value, $expire)
        {
            if($this->_memcacheInstance && $this->checkStatus()) {

                    return $this->_memcacheInstance->set($key, $value, $this->_useCompression, $expire);

            }
            //throw new \Exception('no cache instance available');
        }

        /**
         * Adds new key pair to cache
         *
         * @param string $key
         * @param string $value
         * @param int    $expire : in seconds
         *
         * @return bool
         *
         * @throws \Exception
         */
        public function add($key, $value, $expire)
        {
            if($this->_memcacheInstance && $this->checkStatus()) {

                return $this->_memcacheInstance->add($key, $value, $this->_useCompression, $expire);

            }
            //throw new \Exception('no cache instance available');
        }

        /**
         * Updates Key pair in cache
         *
         * @param string $key
         * @param string $value
         * @param int    $expire : in seconds
         *
         * @return bool
         *
         * @throws \Exception
         */
        public function replace($key, $value, $expire)
        {
            if($this->_memcacheInstance && $this->checkStatus()) {

                return $this->_memcacheInstance->replace($key, $value, $this->_useCompression, $expire);
            }

            //throw new \Exception('no cache instance available');
        }

        /**
         * @param string $key
         *
         * @return bool
         *
         * @throws \Exception
         */
        public function delete($key)
        {
            if($this->_memcacheInstance && $this->checkStatus()) {
                    return $this->_memcacheInstance->delete( $key);
            }

            //throw new \Exception('no cache instance available');
        }

        /**
         * @param $key
         *
         * @return string
         *
         * @throws \Exception
         */
        public function get($key)
        {
            if($this->_memcacheInstance) {
                try {
                    return $this->_memcacheInstance->get( $key );
                } catch (\UnexpectedValueException $ex) {
                    //This type of exception can occur during deserialization
                    //when objects have in-memory references that no longer
                    //exist.  We'll consider this a cache fault.
                }
            }

            //throw new \Exception('no cache instance available');
        }
    }
}