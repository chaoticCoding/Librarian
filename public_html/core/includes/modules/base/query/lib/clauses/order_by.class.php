<?php

namespace core\query\clauses
{
    /**
     * Class order_by_clause
     */
    class order_by extends \core\query\clauses
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
                $result .= $this->getOrderByOutput();
                $result .= $this->getOrderByAttributesOutput();
            }

            return $result;
        }

        /**
         * @return string
         */
        private function getOrderByOutput()
        {
            $result = "ORDER BY ";

            return $result;
        }

        /**
         * @return string
         */
        private function getOrderByAttributesOutput()
        {
            $result = "";

            $count = 0;
            foreach ($this->_attributes as $attribute) {
                if ($count > 0) {
                    $result .= ", ";
                }

                $result .= $attribute;

                $count++;
            }

            return $result;
        }
    }
}