<?php

/**
 *
 */
namespace core\base\collection
{
    /**
     * Class collection_factory
     * @package core\base
     */
    abstract class _factory
    {
        /**
         *
         */
        public static function getTraits()
        {
        }

        /** @var \contact_collection */
        protected static $_objects = [];

        /**
         * since we are using active record pattern, every object request from a
         * factory could result in a database call to get the object structure.
         * This function acts a buffer.
         *
         * @param $className
         *
         * @return mixed
         */
        public static function cachedInstance($className)
        {
            if (!isset(self::$_objects[$className])) {
                self::$_objects[$className] = new $className();
                self::$_objects[$className]->loadFields();
            }

            return clone self::$_objects[$className];
        }

        /**
         * @param $baseLibraryLocation
         * @param $classLocation
         * @param $collectionClassName
         */
        public static function loadItemClasses($baseLibraryLocation, $classLocation, $collectionClassName)
        {
            $baseDir = LOCAL_LIB_FS_PATH . '/' . $baseLibraryLocation . '/lib/';

            if (trim($classLocation) != '') {
                $baseDir .= $classLocation . '/';
            }

            $collectionPath = $baseDir . $collectionClassName . '.class.php';
            $collectionItemPath = $baseDir . $collectionClassName . '_item' . '.class.php';

            require_once $collectionPath;
            require_once $collectionItemPath;
        }
    }
}