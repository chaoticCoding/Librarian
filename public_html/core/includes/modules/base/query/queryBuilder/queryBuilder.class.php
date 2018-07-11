<?php

/**
 *
 */
namespace core\queryBuilder
{
    /**
     * Class queryBuilder
     * @package core
     */
    class Builder extends \core\query\queryAbstract
    {
        /** @var \core\query\queryCache\CachingClient */
        private $_queryCache;

        /** TODO - Like remove, as these serve little use in this pattern
         *
         */
        /*public function loadAll()
        {
            $this->setDefaultSelect();

            /*
            $query = datasource::get(DATASOURCE_ID)->prepare("
                SELECT *
                FROM {$this->_table}
            ");

            $this->loadFromQuery($query);

        }*/

        /** TODO - Like remove, as these serve little use in this pattern
         * @param $limit
         * @param $offset
         */
        /*public function loadByLimitOffset($limit, $offset)
        {
            $this->setDefaultSelect();
            $this->limit($limit, $offset);

            /*
            $query = datasource::get(DATASOURCE_ID)->prepare("
                SELECT *
                FROM {$this->_table}
                LIMIT {$limit} OFFSET {$offset}
            ");

            //$query->bindValue(':table', $this->_table);
            //$query->bindValue(':limit', $limit, PDO::PARAM_INT);
            //$query->bindValue(':offst', $offset,PDO::PARAM_INT);

            $this->loadFromQuery($query);

        }*/


        public function registerQueryCache($queryCache)
        {
            $this->_queryCache = $queryCache;
        }

        /**
         * @return int
         *
         * @throws \Exception
         */
        public function executeCountSelectQuery($cacheable = true)
        {
            $query = $this->getPreparedCountQuery();

            $results = $this->getResultCount($query, $cacheable);

            return $results;

        }

        /**
         * @return \stdClass[]
         *
         * @throws \Exception
         */
        public function executeSelectQuery($cacheable = true)
        {
            $query = $this->getPreparedQuery();

            $results = $this->getResultRecords($query, $cacheable);

            return $results;
        }

        /**
         * @return \PDOStatement|\datasource_EPDOStatement
         */
        public function getPreparedQuery()
        {
            $query = $this->prepareQuery($this->getSelectQuery(), $this->getSelectQueryArgs());

            return $query;
        }

        /**
         * @return \PDOStatement|\datasource_EPDOStatement
         */
        public function getPreparedCountQuery()
        {
            $query = $this->prepareQuery($this->getCountQueryOfSelectQuery(), $this->getSelectQueryArgs());

            return $query;
        }

        /**
         *
         * @param \PDOStatement|\datasource_EPDOStatement $query
         *
         * @return \stdClass[]
         *
         * @throws \Exception
         */
        public function getResultRecords($query, $cacheable = true)
        {
            $records = [];

            //try {
                // try cache
                $cached = null;
                if($cacheable === true && null !== $this->_queryCache) {
                    // is Caching available
                    if($this->_queryCache->status() === true) {
                        //print ("collected From cache");
                        $cached = $this->_queryCache->retrieveQuery($query);

                        // if cached returns something
                        if(null !== $cached) {
                            return $cached;
                        }
                    }
                }

                if ($queryResults = $this->executeQuery($query, $cacheable)) {
                    //print ("collected From mysql");

                    while ($record = $queryResults->fetch(\PDO::FETCH_OBJ)) {
                        $records[] = $record;
                    }

                    if($cacheable === true && null !== $this->_queryCache) {
                        //print ("stored in cache");
                        $this->_queryCache->storeQuery($query, $records);
                    }

                    return $records;

                }
            /*} catch (\Throwable $e) {
            } catch (\Exception $e) {

            }*/

            //throw new \Exception('Query unavailable');


            /*$this->clearQuery();

            return $records;*/
        }

        /**
         * @param \PDOStatement|\datasource_EPDOStatement $query
         *
         * @return \int
         *
         * @throws \Exception
         */
        protected function getResultCount($query, $cacheable = true)
        {
            try {
                if ($queryResults = $this->executeQuery($query, $cacheable)) {
                    return $queryResults->fetchColumn();

                }
            } catch (\Throwable $e) {
            } catch (\Exception $e) {

            }

            throw new \Exception('Column unavailable');
        }

        /** <<<<<< PDO calls  >>>>>> */
        /** TODO these functions should be moved to their respective classes then callbacks registered
         *
         */

        /** <<<<<< prepare QUERY >>>>>> */

        /**
         * @param $querySQL
         *
         * @param $args
         *
         * @return \PDOStatement|\datasource_EPDOStatement
         */
        public function prepareQuery($querySQL, $args)
        {
            $query = \datasource::get(DATASOURCE_ID)->prepare($querySQL);

            if (count($args) > 0) {
                foreach ($args as $bindKey => $bindValue) {
                    $query->bindValue($bindKey, $bindValue);
                }
            }

            return $query;
        }

        /** <<<<<< EXECUTE QUERY >>>>>> */

        /**
         * takes built query and executes with PDO
         * @param \datasource_EPDOStatement $query
         *
         * @return mixed
         *
         * @throws \Exception
         */
        protected function executeQuery($query, $cacheable = true)
        {
            //try {
                if ($query->execute()) {
                    if (LOG_DATABASE_QUERIES === TRUE) {
                        $this->logPreparedStatement($query);
                    }

                    return $query;
                }

            //} catch (\Throwable $e) {
            //} catch (\Exception $e) {

            //}

            //$this->logPreparedStatement($query);
            //syslog(LOG_ERR, "SQL Error - unable to load items. Error: \n" . print_r($query->errorInfo(), true));
            //throw new \Exception('SQL Error - unable to load items. ');

        }


        /** <<<<<< LOGGING >>>>>> */
        /**
         * @param \datasource_EPDOStatement $query
         */
        private function logPreparedStatement($query)
        {
            ob_start();
            $query->debugDumpParams();
            $queryLog = ob_get_contents();
            ob_end_clean();

            syslog(LOG_INFO, $queryLog);
        }
    }
}