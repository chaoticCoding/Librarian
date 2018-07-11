<?php

/**
 *
 */
namespace core\query\queryCache
{
    /**
     * Class CachingClient
     *
     * @package core\query\queryCache
     */
    class CachingClient implements \core\query\queryCache\CachingClient_interface
    {
        /** @var \core\datasource\cachingDAO_interface */
        private $_DAO;

        /** \callable */
        private $_Hasher;

        /** \callable */
        private $_Seralizer;
        private $_Unseralizer;

        private $_Expiration = 200;
        /**
         * CachingClient constructor.
         * @param \core\datasource\cachingDAO_interface|null $cachingDAO
         */
        public function __construct ($cachingDAO = null, $hasher = null, $serializer = null)
        {

            if(!is_null($cachingDAO)) {
                $this->setCachingDAO($cachingDAO);
            }

            if(!is_null($hasher)) {
                $this->setHasher($hasher);
            } else {

                $this->setHasher('\\md5');
            }

            if(!is_null($serializer)) {
                $this->setHasher($serializer);
            } else {

                $this->setSerializer('\\serialize', '\\unserialize');
            }
        }

        /**
         * @param \core\datasource\cachingDAO_interface $cachingDAO
         */
        public function setCachingDAO($cachingDAO)
        {
            $this->_DAO = $cachingDAO;
        }

        public function setHasher(callable $hasher)
        {
            if(is_callable($hasher, false, $callable_name)) {
                $this->_Hasher = $hasher;
            }
        }

        public function setSerializer(callable $serializer, callable $unserializer)
        {
            if(is_callable($serializer, false, $callable_name) && is_callable($unserializer, false, $callable_name)) {
                $this->_Seralizer = $serializer;
                $this->_Unseralizer = $unserializer;
            }
        }

        /**
         * @return bool
         */
        public function status()
        {
            return $this->_DAO->checkStatus();
        }


        /**
         * @return bool
         */
        public function extendedStatus()
        {
            return $this->_DAO->getExtendedStats();
        }

        /**
         * Uses hasher from callback to create hash of extrapolated query
         *
         * @param \string $queryStatement
         *
         * @return \string
         */
        public function getQueryHash($queryStatement)
        {
            return call_user_func($this->_Hasher, $queryStatement);
        }

        /**
         * Uses hasher from callback to create hash of extrapolated query
         *
         * @param \string $queryStatement
         *
         * @return \string
         */
        public function getQuerySerialized($queryResults)
        {
            return call_user_func($this->_Seralizer, $queryResults);
        }

        /**
         * Uses hasher from callback to create hash of extrapolated query
         *
         * @param \string $queryStatement
         *
         * @return \string
         */
        public function getQueryUnserialized($queryResults)
        {
            return call_user_func($this->_Unseralizer, $queryResults);
        }

        /**
         * @param \datasource_EPDOStatement|\PDOStatement $query
         *
         * @return \string
         *
         * @throws \Exception
         */
        public function getInterpolatedQuery($query)
        {
            return $query->interpolateQuery();
        }

        /**
         * @param \datasource_EPDOStatement|\PDOStatement $query
         * @param \stdClass[]                             $results
         *
         * @throws \Exception
         */
        public function storeQuery($query, $results)
        {
            if($this->status() === true) {
                $queryStatement = $this->getInterpolatedQuery($query);

                $queryStatementHash = $this->getQueryHash($queryStatement);

                $querySeralizedResults = $this->getQuerySerialized($results);

                $this->_DAO->set($queryStatementHash, $querySeralizedResults, $this->_Expiration);

            }
            //throw new \Exception("No caching instance available", 0 );

        }

        /**
         * @param \datasource_EPDOStatement|\PDOStatement $query
         *
         * @throws \Exception
         */
        public function retrieveQuery($query)
        {
            if($this->status() === true) {
                $queryStatement = $this->getInterpolatedQuery($query);

                $queryStatementHash = $this->getQueryHash($queryStatement);

                $seralizedResults = $this->_DAO->get($queryStatementHash);

                $results = $this->getQueryUnserialized($seralizedResults);

                return $results;

            }
            //throw new \Exception("No caching instance available", 0 );
        }
    }
}