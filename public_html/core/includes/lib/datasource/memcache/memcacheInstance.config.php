<?php

/**
 * @package core\datasource\memcache
 */
namespace core\datasource\memcache
{
    /**
     * Class config
     * @package core\datasource\memcache
     */
    class instanceConfig
    {
        /** @var \string : Internal name to reference this memcache instance under */
        public $name;

        /** @var \core\datasource\memcache\serverConfig[] */
        public $poolConfig;

        /**
         * instanceConfig constructor.
         *
         * @param string null                                   $name
         * @param \core\datasource\memcache\serverConfig[]      $poolConfig
         */
        public function __construct ($name = 'default', $poolConfig = null)
        {
            $this->name = $name;

            if(!is_null($poolConfig)) {
                $this->addHost($poolConfig);
            }
        }

        /**
         * @param $poolConfig
         */
        public function addHost($poolConfig)
        {
            $this->poolConfig[] = $poolConfig;
        }

        /**
         * @return \core\datasource\memcache\serverConfig[]
         */
        public function getHosts()
        {
            return $this->poolConfig;
        }

    }
}