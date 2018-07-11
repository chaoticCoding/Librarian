<?php

namespace core
{
    require_once('query.factory.php');
    require_once('query.abstract.php');

    require_once('queryBuilder/queryBuilder.controller.php');

    require_once('queryCache/queryCache.controller.php');


    /**
     * Class controller
     * @package core\query
     */
    class query
    {
        /**
         * @param null      $table
         * @param \string[] $select
         *
         * @return \core\queryBuilder\Builder
         */
        public static function getQueryBuilder($table = null, $select = [])
        {
            $queryBuilder = \core\queryBuilder\factory::newBuilder($table);
            $queryBuilder->select($select);

            return $queryBuilder;
        }

        /**
         * @return \core\query\queryCache\CachingClient
         */
        public static function getQueryCache()
        {
            return new \core\query\queryCache\CachingClient();
        }

    }
}