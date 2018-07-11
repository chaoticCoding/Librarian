<?php

namespace core\queryBuilder
{
    class factory
    {
        /** @var \core\datasource\memcache\DAO */
        private static $_cachingDAO;

        /**
         * @param $table
         *
         * @return \core\queryBuilder\Builder
         */
        public static function newBuilder($table = null)
        {
            $queryBuilder = new \core\queryBuilder\Builder($table);

            if(null !== self::$_cachingDAO)
            {
                $queryBuilder->registerQueryCache(self::$_cachingDAO);
            }

            return $queryBuilder;
        }

        /**
         * @param $cachingDAO
         */
        public static function registerCacheHandler($cachingDAO)
        {
            self::$_cachingDAO = $cachingDAO;
        }
    }
}