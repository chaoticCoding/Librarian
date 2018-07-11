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
    class serverConfig
    {
        /** @var \string : External Host to connect to */
        public $host;

        /** @var \int : Port assignment to connect through when accessing external service*/
        public $port;

        /** @var \int : timeout duration in seconds, spec default is 1 LEAVE IT ALONE!!!*/
        public $timeout = 1;

        public function __construct ($host = null, $port = null, $timeout = 1)
        {
            $this->host = $host;
            $this->port = $port;
            $this->timeout = $timeout;
        }
    }
}