<?php
/**
 * Created by PhpStorm.
 * User: Shawn
 * Date: 7/24/2015
 * Time: 5:20 PM
 */

/**
 *
 */
namespace core\Database {

    /**
     * Class MySQLPDO - extention to the Databasic.static to abstraction of MySQL through PHP-PDO
     * @package core\Database
     */
    class MySQLPDO
    {

        /** @var \core\Database\MySQLPDO[]  */
        private static $_datasources = [];

        /**
         * @param $key
         *
         * @return \datasource_PDO
         */
        public static function get($key)
        {
            if (array_key_exists($key, self::$_datasources)) {
                return self::$_datasources[$key];
            }

            return null;
        }

        /**
         * @param      $key
         * @param      $dsn
         * @param      $user
         * @param      $pass
         * @param      $options
         * @param bool $debug
         *
         * @return \datasource_PDO
         *
         * @throws Exception
         */
        public static function createPDO($key, $dsn, $user, $pass, $options, $debug = false)
        {
            $pdo = new datasource_PDO($dsn, $user, $pass, $options);

            $pdo->setAttribute(PDO::ATTR_STATEMENT_CLASS, ['datasource_EPDOStatement', [$pdo]]);

            if ($debug === true) {
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } else {
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            }

            //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->debug = $debug;

            self::$_datasources[$key] = $pdo;

            return $pdo;
        }
    }

}