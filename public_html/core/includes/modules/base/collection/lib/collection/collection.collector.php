<?php

/**
 *
 */
namespace core\base\collection
{
    /**
     * Class collection_collector
     * @package core\base
     */
    abstract class _collector extends \ArrayObject
    {
        /** @var \collection_collection_item */
        protected $_itemClass;

        /** @var string */
        protected $_table;

        /** @var */
        private $_count;

        /** @var */
        private $_currentPage;

        /** @var */
        private $_totalPages;

        /** @var */
        private $_previousPage;

        /** @var */
        private $_nextPage;

        /** @var \string[] */
        private $args = [];

        /**  */
        const ARG_PREFIX = ":arg";

        /** @var \select_statement */
        private $_selectStatement;

        /** @var  \from_clause */
        private $_fromClause;

        /** @var  \where_clause */
        private $_whereClause;

        /** @var  \group_clause */
        private $_groupClause;

        /** @var  \having_clause */
        private $_havingClause;

        /** @var  \order_by_clause */
        private $_orderByClause;

        /** @var  \limit_clause */
        private $_limitClause;

        /** @var \string */
        private $_cacheHashKey;

        /**
         * collection_collection constructor.
         * @param null $table
         * @param null $itemClass
         */
        public function __construct($table = null, $itemClass = null)
        {
            $this->_table = $table;

            $this->_itemClass = $itemClass;

            $this->setupQuery();
        }

        /**
         * @return string $_itemClass
         */
        //public function newItem ()
        //{
        //return new $this->cachedInstance($this->_itemClass());
        //return new $this->_itemClass();
        //}

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
         *
         */
        public function loadAll()
        {
            $query = \datasource::get(DATASOURCE_ID)->prepare("
            SELECT *
            FROM {$this->_table}
        ");

            $this->loadFromQuery($query);
        }

        /**
         * @param $limit
         * @param $offset
         */
        public function loadByLimitOffset($limit, $offset)
        {
            $query = \datasource::get(DATASOURCE_ID)->prepare("
                SELECT *
                FROM {$this->_table}
                LIMIT {$limit} OFFSET {$offset}
            ");

            //$query->bindValue(':table', $this->_table);
            //$query->bindValue(':limit', $limit, PDO::PARAM_INT);
            //$query->bindValue(':offst', $offset,PDO::PARAM_INT);

            $this->loadFromQuery($query);
        }

        /**
         * @return $this
         */
        public function prepareLoadAll()
        {
            $this->select();
            return $this;
        }

        /**
         * @param \datasource_EPDOStatement $query
         *
         * @return mixed
         *
         * @throws Exception
         */
        protected function executeQuery($query)
        {
            try {
                if ($query->execute()) {
                    if (LOG_DATABASE_QUERIES === true) {
                        $this->logPreparedStatement($query);
                    }
                    return $query;
                }
            } catch (\Throwable $e) {
            } catch (\Exception $e) {

            }
            $this->logPreparedStatement($query);
            syslog(LOG_ERR, "SQL Error - unable to load items. Error: \n" . print_r($query->errorInfo(), true));
            throw new Exception('SQL Error - unable to load items. ');

        }

        /**
         * @param \datasource_EPDOStatement|\PDOStatement $query
         * @throws Exception
         */
        protected function loadFromQuery($query)
        {
            $this->_cacheHashKey = md5($query->interpolateQuery()); //TODO: replace with something faster(google xxHash)

            if ($cachedCollection = cache::get($this->_cacheHashKey)) {
                foreach ($cachedCollection as $item) {
                    $this[] = $item;
                }
                return;
            }

            if ($query->execute()) {
                if (LOG_DATABASE_QUERIES === true) {
                    $this->logPreparedStatement($query);
                }

                $this->exchangeArray(array());

                while ($record = $query->fetch(PDO::FETCH_ASSOC)) {
                    /** @var \collection_collection_item $temp */
                    $temp = new $this->_itemClass(false);
                    $temp->import($record);
                    $this[] = $temp;
                }

                cache::cacheCollection($this);
            } else {
                $this->logPreparedStatement($query);
                syslog(LOG_ERR, 'SQL Error - unable to load items. Error: ' . print_r($query->errorInfo(), true));
                throw new Exception('SQL Error - unable to load items.');
            }
        }

        /**
         * @param \datasource_EPDOStatement $query
         * @throws Exception
         */
        protected function loadCountFromQuery($query)
        {
            if ($query->execute()) {
                $this->_count = $query->fetchColumn();
            } else {
                $this->logPreparedStatement($query);
                syslog(LOG_ERR, "SQL Error - unable to load items. " . print_r($query->errorInfo()));
                throw new Exception('SQL Error - unable to load items.');
            }
        }

        /**
         * valuable because it performs a multi insert instead of looping through
         * a collection can calling save
         *
         * @throws Exception
         */
        public function save()
        {
            if (count($this) > 0) {
                $fields = '';
                $values = '';
                $updateText = '';

                //build insert statement based on the first object's structure.
                //this is a collection so all items NEED to be the same collection
                //item
                $firstElement = $this[0];

                foreach ($firstElement as $key => $value) {
                    $fields .= "`{$key}`, ";

                    $updateText .= "{$this->_table}.{$key} = VALUES({$this->_table}.{$key}), ";
                }

                $counter = 0;

                //loop through each item and create parameterized query structure
                foreach ($this as $element) {
                    $nextParameterizedString = '(';

                    foreach ($element as $key => $value) {
                        $nextParameterizedString .= ":" . $key . $counter . ", ";
                    }

                    $values .= rtrim($nextParameterizedString, ', ') . '), ';

                    $counter++;
                }

                $values = rtrim($values, ', ');
                $fields = rtrim($fields, ', ');
                $updateText = rtrim($updateText, ', ');

                $query = datasource::get(DATASOURCE_ID)->prepare("
                INSERT INTO {$this->_table} (
                    {$fields}
                ) VALUES {$values}

                ON DUPLICATE KEY

                UPDATE {$updateText}
            ");

                $counter = 0;

                //bind values to the above parameters
                foreach ($this as $element) {
                    foreach ($element as $key => $value) {
                        if ($value == '') {
                            $value = null;
                        }

                        $query->bindValue(":" . $key . $counter, $value);
                    }

                    $counter++;
                }

                if (!$query->execute()) {
                    $this->logPreparedStatement($query);
                    syslog(LOG_ERR, "SQL Error - unable multi insert. " . print_r($query->errorInfo()));
                    throw new Exception('SQL Error on multi inserting.');
                } else {
                    if (LOG_DATABASE_QUERIES === true) {
                        $this->logPreparedStatement($query);
                    }

                    if (count($this) > 0 && !$this[0]->id) {
                        // Need to calculate range of inserted IDs (assumed auto incremented)
                        $rowCount = datasource::get(DATASOURCE_ID)->getRowCount();
                        $firstInsertedId = datasource::get(DATASOURCE_ID)->getLastInsertedId();

                        $lastInsertedId = $firstInsertedId + $rowCount - 1;

                        $loadQueryStr = "
                        SELECT *
                        FROM {$this->_table}
                        WHERE id BETWEEN {$firstInsertedId} AND {$lastInsertedId}
                    ";

                        $loadQuery = datasource::get(DATASOURCE_ID)->prepare($loadQueryStr);

                        $this->loadFromQuery($loadQuery);
                    }
                }
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

        /**
         * @return string
         */
        private function getCountQueryOfSelectQuery()
        {
            $this->buildSelectStatement();

            $query = "";

            if ($this->_selectStatement) {
                $countSelect = query::getSelectStatement();
                $countSelectFrom = query::getFromStatementClause($this->_selectStatement, "COUNT_QUERY");

                $countSelect->setStatements(array("COUNT(*)"));
                $countSelect->setFrom($countSelectFrom);

                $query .= $countSelect->output();
            }

            return $query;
        }

        /**
         *
         */
        private function buildSelectStatement()
        {
            $this->_selectStatement->setFrom($this->_fromClause);
            $this->_selectStatement->setWhere($this->_whereClause);
            $this->_selectStatement->setGroup($this->_groupClause);
            $this->_selectStatement->setHaving($this->_havingClause);
            $this->_selectStatement->setOrderBy($this->_orderByClause);
            $this->_selectStatement->setLimit($this->_limitClause);
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
         * @param $from
         *
         * @return $this
         */
        public function from($from)
        {
            $table = query::getTableComponent($from);
            $this->_fromClause->setTable($table);

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
            $joinTable = query::getTableComponent($table);
            $firstArgument = query::getNamedArgument($arg1);
            $secondArgument = query::getNamedArgument($arg2);
            $joinExpression = query::getNormalExpression($firstArgument, $op, $secondArgument);

            $leftJoin = query::getLeftJoinComponent();
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
            $joinTable = query::getTableComponent($table);
            $customArgument = query::getNamedArgument($customOn);
            $joinExpression = query::getCustomExpression($customArgument);

            $leftJoin = query::getLeftJoinComponent();
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
         * @return normal_expression
         */
        private function getWhereClauseExpression($colArg, $operatorOrValueArg, $optionalValueArg = null)
        {
            $bindArg = $this->getNextArgName();
            $operator = "=";
            $firstArgument = query::getNamedArgument($colArg);
            $secondArgument = query::getValueArgument();

            $secondArgument->setName($bindArg);

            if ($optionalValueArg == null) {
                $this->args[$bindArg] = $operatorOrValueArg;
                $secondArgument->setValue($operatorOrValueArg);
            } else {
                $operator = $operatorOrValueArg;
                $this->args[$bindArg] = $optionalValueArg;
                $secondArgument->setValue($optionalValueArg);
            }

            $expression = query::getNormalExpression($firstArgument, $operator, $secondArgument);

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

            $firstColumnArgument = query::getNamedArgument($colArg1);
            $secondColumnArgument = query::getNamedArgument($colArg2);

            $argument = query::getValueArgument();
            $argument->setName($bindArg);
            $argument->setValue($this->args[$bindArg]);

            $expression = query::getConcatExpression($firstColumnArgument, $secondColumnArgument, $argument);
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

            $firstArgument = query::getNamedArgument($colArg);

            $secondArgument = query::getValueArgument();
            $secondArgument->setName($bindArg1);
            $secondArgument->setValue($this->args[$bindArg1]);

            $thirdArgument = query::getValueArgument();
            $thirdArgument->setName($bindArg2);
            $thirdArgument->setValue($this->args[$bindArg2]);

            $expression = query::getCastDateBetweenExpression($firstArgument, $secondArgument, "AND", $thirdArgument);
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
            $bindArg1 = $this->getNextArgName();
            $this->args[$bindArg1] = $arg;

            $firstArgument = query::getNamedArgument($colArg);

            $secondArgument = query::getValueArgument();
            $secondArgument->setName($bindArg1);
            $secondArgument->setValue($this->args[$bindArg1]);

            $expression = query::getWhereInExpression($firstArgument, $secondArgument);

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
         * @return custom_expression
         */
        private function getCustomWhereExpression($query)
        {
            $customArgument = query::getNamedArgument($query);
            $expression = query::getCustomExpression($customArgument);

            return $expression;
        }

        /**
         * @return string
         */
        private function getNextArgName()
        {
            return self::ARG_PREFIX . count($this->args);
        }

        /**
         * @param int $pageSize
         * @param int $pageStart
         * @return mixed
         */
        public function getPage($pageSize = 5, $pageStart = 0)
        {
            $this->paginate($pageSize, $pageStart);
            $query = $this->executeSelectQuery();
            $this->setupPagination($pageSize, $pageStart);
            return $query;
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
         * @param $pageSize
         * @param $pageStart
         */
        private function paginate($pageSize, $pageStart)
        {
            $this->loadCountSelectQuery();

            $this->limit($pageSize, $pageStart);
        }

        /**
         *
         */
        public function loadEntire()
        {
            $this->loadSelectQuery();
        }

        /**
         * @param int $pageSize
         * @param int $pageStart
         */
        public function loadPage($pageSize = 5, $pageStart = 0)
        {
            $this->paginate($pageSize, $pageStart);
            $this->loadSelectQuery();
            $this->setupPagination($pageSize, $pageStart);
        }

        /**
         * @return stdClass[]
         */
        public function getResultRecords()
        {
            $records = array();
            $query = $this->executeSelectQuery();

            while ($record = $query->fetch(\PDO::FETCH_OBJ)) {
                $records[] = $record;
            }

            $this->clearQuery();

            return $records;
        }

        /**
         *
         */
        private function loadCountSelectQuery()
        {
            $query = $this->prepareQuery($this->getCountQueryOfSelectQuery(), $this->args);

            $this->loadCountFromQuery($query);
        }

        /**
         *
         */
        private function loadSelectQuery()
        {
            $query = $this->prepareQuery($this->getSelectQuery(), $this->args);

            $this->loadFromQuery($query);
        }

        /**
         * @return mixed
         */
        private function executeSelectQuery()
        {
            $query = $this->prepareQuery($this->getSelectQuery(), $this->args);

            return $this->executeQuery($query);
        }

        /**
         * @param $querySQL
         * @param $args
         * @return mixed
         */
        private function prepareQuery($querySQL, $args)
        {
            $query = datasource::get(DATASOURCE_ID)->prepare($querySQL);

            if (count($args) > 0) {
                foreach ($args as $bindKey => $bindValue) {
                    $query->bindValue($bindKey, $bindValue);
                }
            }

            return $query;
        }

        /**
         *
         */
        private function clearQuery()
        {
            $this->args = array();
            $this->setupQuery();
        }

        /**
         * @param $pageSize
         * @param $pageStart
         */
        private function setupPagination($pageSize, $pageStart)
        {
            if ($this->_count > 0) {
                $this->_currentPage = floor($pageStart / $pageSize) + 1;
            } else {
                $this->_currentPage = 1;
            }

            $this->_totalPages = ceil($this->_count / $pageSize);
            if ($this->_totalPages == 0) {
                $this->_totalPages = 1;
            }

            if ($this->_currentPage == 1) {
                $this->_previousPage = 1;
            } else {
                $this->_previousPage = $this->_currentPage - 1;
            }

            if ($this->_currentPage == $this->_totalPages) {
                $this->nextPage = $this->_currentPage;
            } else {
                $this->_nextPage = $this->_currentPage + 1;
            }
        }

        /**
         * @return mixed
         */
        public function getCurrentPage()
        {
            return $this->_currentPage;
        }

        /**
         * @return mixed
         */
        public function getTotalPages()
        {
            return $this->_totalPages;
        }

        /**
         * @return mixed
         */
        public function getPreviousPage()
        {
            return $this->_previousPage;
        }

        /**
         * @return mixed
         */
        public function getNextPage()
        {
            return $this->_nextPage;
        }

        /**
         * @return mixed
         */
        public function getTotalResults()
        {
            return $this->_count;
        }

        /**
         * @param $totalResults
         * @param $currentPage
         * @param $pageSize
         * @param $pageLimit
         */
        private function setPages($totalResults, $currentPage, $pageSize, $pageLimit)
        {
            $totalPages = ceil($totalResults / $pageSize);
            if ($totalPages > $pageLimit) {
                $totalPages = $pageLimit;
            }

            $nextPage = $currentPage + 1;
            if ($nextPage > $totalPages) {
                $nextPage = $totalPages;
            }

            $previousPage = $currentPage - 1;
            if ($previousPage < 1) {
                $previousPage = 1;
            }

            $this->_currentPage = $currentPage;
            $this->_previousPage = $previousPage;
            $this->_nextPage = $nextPage;
            $this->_totalPages = $totalPages;
            $this->_count = $totalResults;
        }

        /**
         * Paginates a preloaded collection.
         *
         * @param int $pageSize
         * @param int $page
         * @param int $pageLimit
         */
        public function retrievePage($pageSize = 10, $page = 1, $pageLimit = 10)
        {
            // setup page limit
            if ($page > $pageLimit) {
                $page = $pageLimit;
            }

            // setup pages for pagination
            $this->setPages(count($this), $page, $pageSize, $pageLimit);

            // setup page offset
            $pageStart = ($page - 1) * $pageSize;
            $offset = $pageStart;
            $endIndex = $offset + $pageSize;
            $results = array();

            // load array
            while ($offset < $endIndex) {
                if (array_key_exists($offset, $this)) {
                    $results[] = $this[$offset];
                } else {
                    break;
                }
                $offset++;
            }

            $this->exchangeArray($results);
        }

        /**
         * @return string
         */
        public function getTableName()
        {
            return $this->_table;
        }

        /**
         * @return mixed
         */
        public function getCacheHashKey()
        {
            return $this->_cacheHashKey;
        }

        /**
         * @param \datasource_EPDOStatement $query
         */
        private function logPreparedStatement($query)
        {
            ob_start();
            $query->debugDumpParams();
            $queryLog = ob_get_contents();
            ob_end_clean();

            syslog(LOG_INFO, $queryLog);
        }
    }
}