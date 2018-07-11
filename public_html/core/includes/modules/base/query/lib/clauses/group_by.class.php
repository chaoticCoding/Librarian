<?php

/**
 * @package core\query
 */
namespace core\query\clauses
{
    /**
     * Class group_clause
     */
    class group_by extends \core\query\clauses
    {
        /** @var string[] */
        private $_attributes = array();

        /**
         * @param string $attribute
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
                $result .= $this->getGroupByOutput();
                $result .= $this->getGroupAttributesOutput();
            }

            return $result;
        }

        /**
         * @return string
         */
        private function getGroupByOutput()
        {
            $result = "GROUP BY ";

            return $result;
        }

        /**
         * @return string
         */
        private function getGroupAttributesOutput()
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