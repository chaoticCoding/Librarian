<?php

/**
 *
 */
namespace core\query
{
    /**
     * Class factory
     * @package core\query
     */
    abstract class queryAbstract extends \ArrayObject
    {
        /** @var \string */
        protected $_table;


        /** @var \string[] */
        private $args = [];

        /** \string */
        const ARG_PREFIX = ":arg";

        /** @var \core\query\statements\select */
        private $_selectStatement;

        /** @var  \core\query\clauses\from */
        private $_fromClause;

        /** @var  \core\query\clauses\where */
        private $_whereClause;

        /** @var  \core\query\clauses\group_by */
        private $_groupClause;

        /** @var  \core\query\clauses\having */
        private $_havingClause;

        /** @var  \core\query\clauses\order_by */
        private $_orderByClause;

        /** @var  \core\query\clauses\limit */
        private $_limitClause;


        /**
         * collection_collection constructor.
         * @param null|string $table
         */
        public function __construct($table = null)
        {
            $this->_table = $table;

            $this->setupQuery();
        }

        /**
         * @return string
         */
        public function getTableName()
        {
            return $this->_table;
        }

        /**
         *
         */
        private function setupQuery()
        {
            $this->_selectStatement = \core\query\factory::getSelectStatement();
            $this->_fromClause = \core\query\factory::getFromClause();
            $this->_whereClause = \core\query\factory::getWhereClause();
            $this->_groupClause = \core\query\factory::getGroupClause();
            $this->_havingClause = \core\query\factory::getHavingClause();
            $this->_orderByClause = \core\query\factory::getOrderByClause();
            $this->_limitClause = \core\query\factory::getLimitClause();

            $this->setDefaultSelect();
            $this->setDefaultFromClause();
        }

        /**
         * @param array $args
         *
         * @return $this
         */
        public function select($args = array())
        {
            if (count($args) > 0) {
                $this->_selectStatement->setStatements($args);
            } else {
                $this->setDefaultSelect();
            }

            return $this;
        }

        /**
         *
         */
        private function setDefaultSelect()
        {
            if ($this->_table) {
                $defaultStatements = [$this->_table . '.*'];
                $this->_selectStatement->setStatements($defaultStatements);
            }
        }

        /**
         *
         */
        private function setDefaultFromClause()
        {
            if ($this->_table) {
                $table = \core\query\factory::getTableComponent($this->_table);
                $this->_fromClause->setTable($table);
            }
        }



        /**
         * @return mixed
         */
        public function getSelectQuery()
        {
            $this->buildSelectStatement();

            return $this->_selectStatement->output();
        }

        public function getSelectQueryArgs()
        {
            return $this->args;
        }
        /**
         * @return string
         */
        public function getCountQueryOfSelectQuery()
        {
            $this->buildSelectStatement();

            $query = "";

            if ($this->_selectStatement) {
                $countSelect = \core\query\factory::getSelectStatement();

                $countSelectFrom = \core\query\factory::getFromStatementClause($this->_selectStatement, "COUNT_QUERY");

                $countSelect->setStatements(
                                                ["COUNT(*)"]
                                            );

                $countSelect->setFrom($countSelectFrom);

                $query .= $countSelect->output();
            }

            return $query;
        }

        /**
         *
         */
        public function clearQuery()
        {
            $this->args = array();
            $this->setupQuery();
        }

        /**
         *
         */
        public function buildSelectStatement()
        {
            $this->_selectStatement->setFrom($this->_fromClause);
            $this->_selectStatement->setWhere($this->_whereClause);
            $this->_selectStatement->setGroup($this->_groupClause);
            $this->_selectStatement->setHaving($this->_havingClause);
            $this->_selectStatement->setOrderBy($this->_orderByClause);
            $this->_selectStatement->setLimit($this->_limitClause);
        }

        /**
         * @param $from
         *
         * @return $this
         */
        public function from($from)
        {
            $table = \core\query\factory::getTableComponent($from);

            $this->_fromClause->setTable($table);

            return $this;
        }

        /**
         * @param \string $table
         * @param \string $arg1
         * @param \string $op
         * @param \string $arg2
         * @param \string $method
         *
         * @return $this
         */
        public function Join($table, $arg1, $op, $arg2, $method = "")
        {
            $joinTable = \core\query\factory::getTableComponent($table);
            $firstArgument = \core\query\factory::getNamedArgument($arg1);
            $secondArgument = \core\query\factory::getNamedArgument($arg2);
            $joinExpression = \core\query\factory::getNormalExpression($firstArgument, $op, $secondArgument);

            $leftJoin = \core\query\factory::getJoinComponent();
            $leftJoin->setTable($joinTable);
            $leftJoin->setOn($joinExpression);
            $leftJoin->setJoinMethod($method);

            $this->_fromClause->addJoin($leftJoin);

            return $this;
        }

        /**
         * @param $table
         * @param $arg1
         * @param $op
         * @param $arg2
         *
         * @return $this
         */
        public function leftJoin($table, $arg1, $op, $arg2)
        {
            $joinTable = \core\query\factory::getTableComponent($table);
            $firstArgument = \core\query\factory::getNamedArgument($arg1);
            $secondArgument = \core\query\factory::getNamedArgument($arg2);
            $joinExpression = \core\query\factory::getNormalExpression($firstArgument, $op, $secondArgument);

            $leftJoin = \core\query\factory::getLeftJoinComponent();
            $leftJoin->setTable($joinTable);
            $leftJoin->setOn($joinExpression);

            $this->_fromClause->addJoin($leftJoin);

            return $this;
        }

        /**
         * @param $table
         * @param $customOn
         *
         * @return $this
         */
        public function customLeftJoin($table, $customOn)
        {
            $joinTable = \core\query\factory::getTableComponent($table);
            $customArgument = \core\query\factory::getNamedArgument($customOn);
            $joinExpression = \core\query\factory::getCustomExpression($customArgument);

            $leftJoin = \core\query\factory::getLeftJoinComponent();
            $leftJoin->setTable($joinTable);
            $leftJoin->setOn($joinExpression);

            $this->_fromClause->addJoin($leftJoin);

            return $this;
        }

        /**
         * @param $colArg
         * @param $operatorOrValueArg
         * @param null $optionalValueArg
         *
         * @return $this
         */
        public function where($colArg, $operatorOrValueArg, $optionalValueArg = null)
        {
            $expression = $this->getWhereClauseExpression($colArg, $operatorOrValueArg, $optionalValueArg);

            $this->_whereClause->addANDExpression($expression);

            return $this;
        }

        /**
         * @param $colArg
         * @param $operatorOrValueArg
         * @param null $optionalValueArg
         *
         * @return $this
         */
        public function orWhere($colArg, $operatorOrValueArg, $optionalValueArg = null)
        {
            $expression = $this->getWhereClauseExpression($colArg, $operatorOrValueArg, $optionalValueArg);

            $this->_whereClause->addORExpression($expression);

            return $this;
        }

        /**
         * @param $colArg
         * @param $operatorOrValueArg
         * @param null $optionalValueArg
         *
         * @return \core\query\expressions\normal
         */
        public function getWhereClauseExpression($colArg, $operatorOrValueArg, $optionalValueArg = null)
        {
            $bindArg = $this->getNextArgName();
            $operator = "=";
            $firstArgument = \core\query\factory::getNamedArgument($colArg);
            $secondArgument = \core\query\factory::getValueArgument();

            $secondArgument->setName($bindArg);

            if ($optionalValueArg == null) {
                $this->args[$bindArg] = $operatorOrValueArg;
                $secondArgument->setValue($operatorOrValueArg);
            } else {
                $operator = $operatorOrValueArg;
                $this->args[$bindArg] = $optionalValueArg;
                $secondArgument->setValue($optionalValueArg);
            }

            $expression = \core\query\factory::getNormalExpression($firstArgument, $operator, $secondArgument);

            return $expression;
        }

        /**
         * @param $colArg1
         * @param $colArg2
         * @param $arg
         *
         * @return $this
         */
        public function concatWhere($colArg1, $colArg2, $arg)
        {
            $bindArg = $this->getNextArgName();
            $this->args[$bindArg] = $arg;

            $firstColumnArgument = \core\query\factory::getNamedArgument($colArg1);
            $secondColumnArgument = \core\query\factory::getNamedArgument($colArg2);

            $argument = \core\query\factory::getValueArgument();
            $argument->setName($bindArg);
            $argument->setValue($this->args[$bindArg]);

            $expression = \core\query\factory::getConcatExpression($firstColumnArgument, $secondColumnArgument, $argument);
            $this->_whereClause->addANDExpression($expression);

            return $this;
        }

        /**
         * @param $colArg
         * @param $arg2
         * @param $arg3
         *
         * @return $this
         */
        public function whereBetween($colArg, $arg2, $arg3)
        {
            $bindArg1 = $this->getNextArgName();
            $this->args[$bindArg1] = $arg2;

            $bindArg2 = $this->getNextArgName();
            $this->args[$bindArg2] = $arg3;

            $firstArgument = \core\query\factory::getNamedArgument($colArg);

            $secondArgument = \core\query\factory::getValueArgument();
            $secondArgument->setName($bindArg1);
            $secondArgument->setValue($this->args[$bindArg1]);

            $thirdArgument = \core\query\factory::getValueArgument();
            $thirdArgument->setName($bindArg2);
            $thirdArgument->setValue($this->args[$bindArg2]);

            $expression = \core\query\factory::getCastDateBetweenExpression($firstArgument, $secondArgument, "AND", $thirdArgument);
            $this->_whereClause->addANDExpression($expression);

            return $this;
        }

        /** TODO EXTEND for array map to allow objects or groups of objects to be passed in. for now must be array of vals
         * function for building where in cases
         *
         * @param $colArg
         * @param $arg
         *
         * @return $this
         */
        public function whereIn($colArg, $arg)
        {
            //$bindArg1 = $this->getNextArgName();
            //$this->args[$bindArg1] = ""; //$arg;

            $firstArgument = \core\query\factory::getNamedArgument($colArg);

            $secondArgument = \core\query\factory::getValueArgument();
            $secondArgument->setName($colArg);
            $secondArgument->setValue($arg); //$this->args[$bindArg1]);

            $expression = \core\query\factory::getWhereInExpression($firstArgument, $secondArgument);

            $this->_whereClause->addANDExpression($expression);

            return $this;
        }

        /**
         * @param $col
         * @return $this
         */
        public function groupBy($col)
        {
            $this->_groupClause->addAttribute($col);

            return $this;
        }

        /**
         * @param $queryLine
         * @return $this
         */
        public function having($queryLine)
        {
            $this->_havingClause->addAttribute($queryLine);

            return $this;
        }

        /**
         * @param $col
         * @param $arg
         * @return $this
         */
        public function orderBy($col, $arg)
        {
            $queryLine = $col . " " . $arg;

            $this->_orderByClause->addAttribute($queryLine);

            return $this;
        }

        /**
         * @param $query
         * @return $this
         */
        public function customWhere($query)
        {
            $expression = $this->getCustomWhereExpression($query);

            $this->_whereClause->addANDExpression($expression);

            return $this;
        }

        /**
         * @param $query
         * @return $this
         */
        public function customOrWhere($query)
        {
            $expression = $this->getCustomWhereExpression($query);

            $this->_whereClause->addORExpression($expression);

            return $this;
        }

        /**
         * @param $query
         * @return \core\query\expressions\custom
         */
        public function getCustomWhereExpression($query)
        {
            $customArgument = \core\query\factory::getNamedArgument($query);
            $expression = \core\query\factory::getCustomExpression($customArgument);

            return $expression;
        }

        /**
         * @param $limit
         * @param $offset
         */
        public function limit($limit, $offset)
        {
            $this->_limitClause->setSize($limit);
            $this->_limitClause->setOffset($offset);
        }

        /**
         * @return string
         */
        private function getNextArgName()
        {
            return self::ARG_PREFIX . count($this->args);
        }

    }
}