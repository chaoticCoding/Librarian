<?php

/**
 * @package core\query
 */
namespace core\query\clauses
{
    /**
     * Class having_clause
     */
    class having extends \core\query\clauses
    {
        /** @var string[] */
        private $_attributes = array();

        /**
         * @param $attribute
         */
        public function addAttribute($attribute)
        {
            $this->_attributes[] = $attribute;
        }

        /**
         * @return string
         */
        public function output()
        {
            $result = "";

            if (count($this->_attributes) > 0) {
                $result .= $this->getHavingOutput();
                $result .= $this->getHavingAttributesOutput();
            }

            return $result;
        }

        /**
         * @return string
         */
        private function getHavingOutput()
        {
            $result = "HAVING ";

            return $result;
        }

        /**
         * @return string
         */
        private function getHavingAttributesOutput()
        {
            $result = "";

            $count = 0;
            foreach ($this->_attributes as $attribute) {
                if ($count > 0) {
                    $result .= " AND ";
                }

                $result .= $attribute;

                $count++;
            }

            return $result;
        }
    }
}