<?php

namespace core\query\components
{
    /**
     * Class table_component
     */
    class table extends \core\query\components
    {
        /** @var string */
        private $_tableName;

        /**
         * table_component constructor.
         *
         * @param $tableName
         */
        public function __construct($tableName)
        {
            $this->_tableName = $tableName;
        }

        /**
         * @return string
         */
        public function output()
        {
            return $this->_tableName;
        }
    }
}