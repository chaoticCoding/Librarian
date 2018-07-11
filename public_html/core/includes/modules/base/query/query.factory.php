<?php

namespace core\query
{
    require_once('lib/expressions/expression.abstract.php');
    require_once('lib/components/components.abstract.php');
    require_once('lib/clauses/clauses.abstract.php');
    require_once('lib/statements/statements.abstract.php');
    require_once('lib/arguments/argument.abstract.php');

    /**
     * Class query
     * @package core
     */
    class factory
    {
        /**
         * @param \core\query\arguments  $firstArgument
         * @param \string                $operator
         * @param \core\query\arguments  $secondArgument
         *
         * @return \core\query\expressions\normal
         */
        public static function getNormalExpression(\core\query\arguments $firstArgument, $operator, \core\query\arguments $secondArgument)
        {
            require_once('lib/expressions/normal.class.php');
            return new \core\query\expressions\normal($firstArgument, $operator, $secondArgument);
        }

        /**
         * @param \core\query\arguments   $firstArgument
         * @param \core\query\arguments[] $secondArgument
         *
         * @return \core\query\expressions\in
         */
        public static function getWhereInExpression(\core\query\arguments $firstArgument, \core\query\arguments $secondArgument)
        {
            require_once('lib/expressions/in.class.php');
            return new \core\query\expressions\in($firstArgument, $secondArgument);
        }

        /**
         * @param \core\query\arguments $firstArgument
         * @param \core\query\arguments $secondArgument
         * @param \string               $operator
         * @param \core\query\arguments $thirdArgument
         *
         * @return \core\query\expressions\cast_date_between
         */
        public static function getCastDateBetweenExpression(\core\query\arguments $firstArgument, \core\query\arguments $secondArgument, $operator, \core\query\arguments $thirdArgument)
        {
            require_once('lib/expressions/cast_date_between.class.php');
            return new \core\query\expressions\cast_date_between($firstArgument, $secondArgument, $operator, $thirdArgument);
        }

        /**
         * @param \core\query\arguments $firstColumnArgument
         * @param \core\query\arguments $secondColumnArgument
         * @param \core\query\arguments $argument
         *
         * @return \core\query\expressions\concat
         */
        public static function getConcatExpression(\core\query\arguments $firstColumnArgument, \core\query\arguments $secondColumnArgument, \core\query\arguments $argument)
        {
            require_once('lib/expressions/concat.class.php');
            return new \core\query\expressions\concat($firstColumnArgument, $secondColumnArgument, $argument);
        }

        /**
         * @param \core\query\arguments      $argument
         *
         * @return \core\query\expressions\custom
         */
        public static function getCustomExpression(\core\query\arguments $argument)
        {
            require_once('lib/expressions/custom.class.php');
            return new \core\query\expressions\custom($argument);
        }

        /**
         * @param $argument
         *
         * @return \core\query\arguments\named
         */
        public static function getNamedArgument($argument)
        {
            require_once('lib/arguments/named.class.php');
            return new \core\query\arguments\named($argument);
        }

        /**
         * @return \core\query\arguments\value
         */
        public static function getValueArgument()
        {
            require_once('lib/arguments/value.class.php');
            return new \core\query\arguments\value();
        }

        /**
         * @param $name
         *
         * @return \core\query\components\table
         */
        public static function getTableComponent($name)
        {
            require_once('lib/components/table.class.php');
            return new \core\query\components\table($name);
        }

        /**
         * @return \core\query\components\left_join
         */
        public static function getLeftJoinComponent()
        {
            require_once('lib/components/left_join.class.php');
            return new \core\query\components\left_join();
        }

        /**
         * @return \core\query\components\join
         */
        public static function getJoinComponent()
        {
            require_once('lib/components/join.class.php');
            return new \core\query\components\join();
        }

        /**
         * @return \core\query\clauses\from
         */
        public static function getFromClause()
        {
            require_once('lib/clauses/from.class.php');
            return new \core\query\clauses\from();
        }

        /**
         * @return \core\query\clauses\where
         */
        public static function getWhereClause()
        {
            require_once('lib/clauses/where.class.php');
            return new \core\query\clauses\where();
        }

        /**
         * @return \core\query\clauses\group_by
         */
        public static function getGroupClause()
        {
            require_once('lib/clauses/group_by.class.php');
            return new \core\query\clauses\group_by();
        }

        /**
         * @return \core\query\clauses\having
         */
        public static function getHavingClause()
        {
            require_once('lib/clauses/having.class.php');
            return new \core\query\clauses\having();
        }

        /**
         * @return \core\query\clauses\order_by
         */
        public static function getOrderByClause()
        {
            require_once('lib/clauses/order_by.class.php');
            return new \core\query\clauses\order_by();
        }

        /**
         * @return \core\query\clauses\limit
         */
        public static function getLimitClause()
        {
            require_once('lib/clauses/limit.class.php');
            return new \core\query\clauses\limit();
        }

        /**
         * @param \core\query\statements $statement
         * @param \string $label
         *
         * @return \core\query\clauses\from_statement
         */
        public static function getFromStatementClause($statement, $label)
        {
            require_once('lib/clauses/from_statement.class.php');
            return new \core\query\clauses\from_statement($statement, $label);
        }

        /**
         * @return \core\query\statements\select
         */
        public static function getSelectStatement()
        {
            require_once('lib/statements/select.class.php');
            return new \core\query\statements\select();
        }
    }
}
