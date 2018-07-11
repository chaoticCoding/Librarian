<?php

namespace core\query\statements
{
    /**
     * Class select_statement
     */
    class select extends \core\query\statements
    {
        /** @var string[] */
        private $_statements = array();

        /** @var \core\query\clauses */
        private $_from;

        /** @var \core\query\clauses */
        private $_where;

        /** @var \core\query\clauses */
        private $_having;

        /** @var \core\query\clauses */
        private $_group;

        /** @var \core\query\clauses */
        private $_orderBy;

        /** @var \core\query\clauses */
        private $_limit;

        /**
         * @param string[] $statements
         */
        public function setStatements($statements = array())
        {
            $this->_statements = $statements;
        }

        /**
         * @return array
         */
        public function getStatements()
        {
            return $this->_statements;
        }

        /**
         * @param \core\query\clauses $from
         */
        public function setFrom(\core\query\clauses $from)
        {
            $this->_from = $from;
        }

        /**
         * @return \core\query\clauses
         */
        public function getFrom()
        {
            return $this->_from;
        }

        /**
         * @param \core\query\clauses $where
         */
        public function setWhere(\core\query\clauses $where)
        {
            $this->_where = $where;
        }

        /**
         * @return \core\query\clauses
         */
        public function getWhere()
        {
            return $this->_where;
        }

        /**
         * @param \core\query\clauses $having
         */
        public function setHaving(\core\query\clauses $having)
        {
            $this->_having = $having;
        }

        /**
         * @return \core\query\clauses
         */
        public function getHaving()
        {
            return $this->_having;
        }

        /**
         * @param \core\query\clauses $group
         */
        public function setGroup(\core\query\clauses $group)
        {
            $this->_group = $group;
        }

        /**
         * @return \core\query\clause
         */
        public function getGroup()
        {
            return $this->_group;
        }

        /**
         * @param \core\query\clauses $orderBy
         */
        public function setOrderBy(\core\query\clauses $orderBy)
        {
            $this->_orderBy = $orderBy;
        }

        /**
         * @return \core\query\clauses
         */
        public function getOrderBy()
        {
            return $this->_orderBy;
        }

        /**
         * @param \core\query\clauses $limit
         */
        public function setLimit(\core\query\clauses $limit)
        {
            $this->_limit = $limit;
        }

        /**
         * @return \core\query\clauses
         */
        public function getLimit()
        {
            return $this->_limit;
        }

        /**
         * @return string
         */
        public function output()
        {
            $result = $this->getSelectOutput();
            $result .= $this->getStatementOutput();
            $result .= $this->getClauseOutput($this->_from);
            $result .= $this->getClauseOutput($this->_where);
            $result .= $this->getClauseOutput($this->_group);
            $result .= $this->getClauseOutput($this->_having);
            $result .= $this->getClauseOutput($this->_orderBy);
            $result .= $this->getClauseOutput($this->_limit);

            return $result;
        }

        /**
         * @return string
         */
        private function getSelectOutput()
        {
            $result = "SELECT";
            $result .= " ";

            return $result;
        }

        /**
         * @return string
         */
        private function getStatementOutput()
        {
            $result = "";

            $count = 0;

            foreach ($this->_statements as $statement) {
                if ($count > 0) {
                    $result .= ", ";
                }

                $result .= $statement;

                $count++;
            }

            if ($count > 0) {
                $result .= " ";
            }

            return $result;
        }

        /**
         * @param \core\query\clauses $clause
         *
         * @return string
         */
        private function getClauseOutput($clause)
        {
            $result = "";

            if ($clause) {
                $result .= $clause->output();
                $result .= " ";
            }

            return $result;
        }
    }
}