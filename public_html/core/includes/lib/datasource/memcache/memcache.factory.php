<?php

/**
 *
 */
namespace core\datasource\memcache
{
    /**
     * Class factory
     * @package core\datasource\memcache
     */
    class factory
    {
        /**
         * builds new memcache config object for passing into DAO
         * @param null $host
         * @param null $port
         * @param int  $timeout
         *
         * @return \core\datasource\memcache\serverConfig
         */
        public static function getServerConfig( $host = null, $port = null, $timeout = 1)
        {
            return new \core\datasource\memcache\serverConfig($host, $port, $timeout);
        }

        /**
         * builds new memcache config object for passing into DAO
         * @param null $host
         * @param null $configPool
         * @param int  $timeout
         *
         * @return \core\datasource\memcache\instanceConfig
         */
        public static function getInstanceConfig( $host = 'default', $configPool = null)
        {
            return new \core\datasource\memcache\instanceConfig($host = 'default', $configPool);
        }

        /**
         * @param \core\datasource\memcache\instanceConfig $instanceConfig
         *
         * @return \core\datasource\memcache\DAO
         */
        public static function createDAO($instanceConfig)
        {
           $newDAO = self::getNewDAO();

           /** @var \core\datasource\memcache\serverConfig $host */
            foreach ($instanceConfig->getHosts() as $server) {
               //var_dump($server);

               $newDAO->addHost($server->host, $server->port);
           }

           return $newDAO;
        }

        /**
         * @return \core\datasource\memcache\DAO
         */
        public static function getNewDAO()
        {
            return new \core\datasource\memcache\DAO();
        }
    }
}