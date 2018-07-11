<?php

namespace core\datasource
{
    require_once ('memcache.factory.php');
    require_once ('memcache.class.php');

    require_once ('memcacheInstance.config.php');
    require_once ('memcacheServer.config.php');

    class memcache implements \core\datasource\cachingDAO_interface
    {
        const DEFAULT_EXPIRE = 100;

        /** @var \core\datasource\memcache\DAO[] */
        private static $_knownCaches;


        /**
         * @param \core\datasource\memcache\instanceConfig $endpoint
         *
         * @throws \Exception
         */
        public static function connect($endpoint)
        {
            $newDAO = \core\datasource\memcache\factory::createDAO($endpoint);

            if (@$newDAO->checkStatus()) {

                self::$_knownCaches[ $endpoint->name ] = $newDAO;

            }

        }

        /**
         * @param $endpointName
         *
         * @return \core\datasource\memcache\DAO
         */
        public static function getEndpoint($endPointName = 'default')
        {
            return self::$_knownCaches[ $endPointName ];
        }

        /**
         * @param string $endPointName
         *
         * @return array|bool
         */
        public static function getEndpoint_ExtendedStats($endPointName = 'default')
        {
            return self::getEndpoint( $endPointName )->getExtendedStats();

        }

        /** <<<<<< CACHE SETTERS >>>>>> */
        /**
         * @param \string      $key
         * @param \string      $value
         * @param \int         $expire
         * @param \string      $endPointName
         *
         * @return bool|null
         *
         * @throws \Exception
         */
        public static function set($key, $value, $expire = self::DEFAULT_EXPIRE, $endPointName = 'default')
        {
            if (!self::$_knownCaches[$endPointName]) {
                return null;
            }

            return self::$_knownCaches[$endPointName]->set($key, $value, $expire);

        }

        /**
         * @param \string      $key
         * @param \string      $value
         * @param \int         $expire
         * @param \string      $endPointName
         *
         * @return bool|null
         *
         * @throws \Exception
         */
        public static function add($key, $value, $expire = self::DEFAULT_EXPIRE, $endPointName = 'default')
        {
            if (!self::$_knownCaches[$endPointName]) {
                return null;
            }


            return self::$_knownCaches[$endPointName]->add($key, $value, $expire);

        }

        /**
         * @param \string      $key
         * @param \string      $value
         * @param \int         $expire
         * @param \string      $endPointName
         *
         * @return bool|null
         *
         * @throws \Exception
         */
        public static function replace($key, $value, $expire = self::DEFAULT_EXPIRE, $endPointName = 'default')
        {
            if (!self::$_knownCaches[$endPointName]) {
                return null;
            }

            return self::$_knownCaches[$endPointName]->replace($key, $value, $expire);

        }

        /** <<<<<< CACHE GETTER >>>>>> */
        /**
         * @param $key
         *
         * @return string|string[]
         *
         * @throws \Exception
         */
        public static function get($key, $endPointName = 'default')
        {
            if (!self::$_knownCaches[$endPointName]) {
                return null;
            }

            return self::$_knownCaches[$endPointName]->get($key);
        }


        /** <<<<<< CACHE DELETE >>>>>> */
        /**
         * @param $key
         *
         * @return bool|null
         *
         * @throws \Exception
         */
        public static function delete($key, $endPointName = 'default')
        {
            if (!self::$_knownCaches[$endPointName]) {
                return null;
            }

            return self::$_knownCaches[$endPointName]->delete($key);
        }


        /** **** TODO move into callbacks as part of the base collector package **** */

        /**  */
        const ITEM_CACHE_TTL_SECONDS = 100;

        /**
         * @param object $object
         * @param string $key
         *
         * @return string
         *
         * @throws \Exception: Object cannot be null
         */
        public static function generateCacheKeyFromClass($object, $key)
        {
            if ($object == null) {
                throw new \Exception("Object can not be null");
            }

            return get_class($object) . '-' . $key;
        }

        /**
         * @param \collection_collection_item $item
         */
        public static function invalidateCollectionsThatContainItem($item, $endPointName = 'default')
        {
            if (!self::$_cachingEnabled) {
                return;
            }

            $table = $item->getTableName();

            if (($queriesForTable = self::get($table)) && (is_array($queriesForTable))) {
                self::delete($table);

                foreach ($queriesForTable as $query) {
                    self::delete($query);
                }
            }
        }

        /**
         * @param \collection_collection $collection
         */
        public static function cacheCollection($collection, $endPointName = 'default')
        {
            if (!self::$_cachingEnabled) {
                return;
            }

            $table = $collection->getTableName();

            if (($queriesForTable = self::get($table)) && (is_array($queriesForTable))) {
                $queriesForTable[] = $collection->getCacheHashKey();
                self::set($table, $queriesForTable, self::ITEM_CACHE_TTL_SECONDS);
            } else {
                self::set($table, array($collection->getCacheHashKey()), self::ITEM_CACHE_TTL_SECONDS);
            }

            self::set($collection->getCacheHashKey(), $collection, self::ITEM_CACHE_TTL_SECONDS);
        }

        /**
         * @param \collection_collection_item $item
         * @param null $value
         */
        public static function cacheItem($item, $value = null, $endPointName = 'default')
        {
            if (!self::$_cachingEnabled) {
                return;
            }

            if ($value == null) {
                $value = $item->getPrimaryKeyValue();
            }

            try {
                $key = self::generateCacheKeyFromClass($item, $value);

                cache::set($key, $item, self::ITEM_CACHE_TTL_SECONDS);
            } catch (\Throwable $e) {
            } catch (\Exception $e) {

            }
        }
    }
}