<?php

/**
 *
 ***/
namespace core\persistence\session
{

    use Aws\DynamoDb\Session\SessionHandler;

    class session_AWS_DynamoDB
    {
        // storage for DynamoDB Table name used for storing session Data
        private static $_dynamoDB_SessionTable = "";

        // Reference of dynamoDBClient
        private static $_dynamoDB_Client = null;

        // Reference to AWS DynamoDB Session Handler
        private static $_dynamoDB_sessionHandler = null;

        /**
         * instance creator for AWS SessionHandler
         *
         * @param (string) $tableName, table override to use instead of name set in APACHE enviroment
         * @param (DynamoDbClient) $dynamoDB_Client : use existing DnamoDBClient instance, if not set new instance is requested
         *
         * @return (SessionHandler) returns created instance
         ***/
        public static function newInstance($tableName, $dynamoDB_Client) {
            if(empty($dynamoDB_Client) || is_null($dynamoDB_Client)) {
                throw new \Exception("Error no dynamoDB Client", 1);
            }

            self::$_dynamoDB_Client = $dynamoDB_Client;

            if(trim($tableName) != "") {
                self::$_dynamoDB_SessionTable = $tableName;

                self::$_dynamoDB_sessionHandler = \Aws\DynamoDb\SessionHandler::fromClient(self::$_dynamoDB_Client, array(
                    'table_name' => trim($tableName),
                ));

                return self::$_dynamoDB_sessionHandler;

            } else {
                throw new \Exception("Error Processing Request, No known Session Table for DynamoDb Session Handler", 1);
            }
        }

        /**
         * Dirty singleton Colletor, as you can only have one active session handler
         *
         * @param (DynamoDbClient) $dynamoDB_Client : use existing DnamoDBClient instance, if not set new instance is requested
         *
         * @return (SessionHandler) connected instance of Aws\DynamoDb\SessionHandler
         ***/
        public static function getInstance() {
            if(!isset(self::$_dynamoDB_sessionHandler)) {
                self::createHandler($dynamoDB_Client, $tableName);
            }

            return self::$_dynamoDB_sessionHandler;
        }

        /**
         * registered session handler
         ***/
        public static function register(){
            if(isset(self::$_dynamoDB_sessionHandler)){
                return self::$_dynamoDB_sessionHandler->register();
            } else {
                throw new \Exception("Error Processing Request, No DyamoDB Session Handler available", 1);
            }
        }
    }
}
