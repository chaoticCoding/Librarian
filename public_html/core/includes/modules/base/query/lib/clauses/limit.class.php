<?php

/**
 * @package core\query
 */
namespace core\query\clauses
{
    /**
     * Class limit_clause
     */
    class limit extends \core\query\clauses
    {
        /** @var int */
        private $_size;

        /** @var int */
        private $_offset;

        /**
         * @param int $size
         */
        public function setSize($size)
        {
            $this->_size = $size;
        }

        /**
         * @param int $offset
         */
        public function setOffset($offset)
        {
            $this->_offset = $offset;
        }

        /**
         * @return string
         */
        public function output()
        {
            $result = "";

            if ($this->_size) {
                $result .= $this->getLimitOutput();
                $result .= $this->getLimitOffset();
            }

            return $result;
        }

        /**
         * @return string
         */
        private function getLimitOutput()
        {
            $result = "LIMIT ";

            $result .= $this->_size;

            $result .= " ";

            return $result;
        }

        /**
         * @return string
         */
        private function getLimitOffset()
        {
            $result = "";

            if ($this->_offset) {
                $result .= "OFFSET ";
                $result .= $this->_offset;
            }

            return $result;
        }
    }
}