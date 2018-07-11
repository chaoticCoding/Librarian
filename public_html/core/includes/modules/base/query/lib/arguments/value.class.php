<?php

/**
 * @package core\query
 */
namespace core\query\arguments
{

    /**
     * Class value
     * @package core\query\arguments
     */
    class value extends \core\query\arguments
    {
        /** @var string */
        private $_name;

        /** @var string */
        private $_value;

        /**
         * @param string $name
         */
        public function setName($name)
        {
            $this->_name = $name;
        }

        /**
         * @return string
         */
        public function getName()
        {
            return $this->_name;
        }

        /**
         * @param mixed $value
         */
        public function setValue($value)
        {
            $this->_value = $value;
        }

        /**
         * @return mixed
         */
        public function getValue()
        {
            return $this->_value;
        }

        /**
         * @return mixed
         */
        public function output()
        {
            return $this->_name;
        }
    }
}