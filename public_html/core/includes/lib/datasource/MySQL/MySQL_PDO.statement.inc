<?php

namespace core\datasource\MySQL_PDO
{
    class statement extends \PDOStatement
    {
        /**
         * The first argument passed in should be an instance of the PDO object. If so, we'll cache it's reference locally
         * to allow for the best escaping possible later when interpolating our query. Other parameters can be added if
         * needed.
         *
         * @param \PDO $pdo
         */
        protected function __construct(\PDO $pdo = null)
        {
            if ($pdo) {
                $this->_pdo = $pdo;
            }
        }

        /**@var \PDO $_pdo */
        protected $_pdo = "";

        /**  @var string $fullQuery - will be populated with the interpolated db query string */
        public $fullQuery;

        /** @var array $boundParams - array of arrays containing values that have been bound to the query as parameters */
        protected $boundParams = array();

        /**
         * Overrides the default \PDOStatement method to add the named parameter and it's reference to the array of bound
         * parameters - then accesses and returns parent::bindParam method
         *
         * @param string        $param
         * @param &string       $value
         * @param int           $datatype
         * @param int|null      $length
         * @param mixed|null    $driverOptions
         *
         * @return bool - default of \PDOStatement::bindParam()
         */
        public function bindParam($param, &$value, $datatype = \PDO::PARAM_STR, $length = null, $driverOptions = null)
        {
            $this->boundParams[$param] = [
                "value"       => &$value,
                "datatype"    => $datatype
            ];

            return parent::bindParam($param, $value, $datatype, $length, $driverOptions);
        }

        /**
         * Overrides the default \PDOStatement method to add the named parameter and it's value to the array of bound values
         * - then accesses and returns parent::bindValue method
         *
         * @param string $param
         * @param string $value
         * @param int $datatype
         *
         * @return bool - default of \PDOStatement::bindValue()
         */
        public function bindValue($param, $value, $datatype = \PDO::PARAM_STR)
        {
            $this->boundParams[$param] = [
                "value"       => $value,
                "datatype"    => $datatype
            ];

            return parent::bindValue($param, $value, $datatype);
        }

        /** TODO, Poor use as this should be using PDO::bindParam()
         * Copies $this->queryString then replaces bound markers with associated values ($this->queryString is not modified
         * but the resulting query string is assigned to $this->fullQuery)
         *
         * @param array $inputParams - array of values to replace ? marked parameters in the query string
         *
         * @return string $testQuery - interpolated db query string
         */
        public function interpolateQuery($inputParams = null)
        {
            $testQuery = $this->queryString;
            $params = ($this->boundParams) ? $this->boundParams : $inputParams;

            if ($params) {
                ksort($params);
                foreach ($params as $key => $value) {
                    $replValue = (is_array($value)) ? $value
                        : [
                            'value'       => $value,
                            'datatype'    => \PDO::PARAM_STR
                        ];

                    $replValue = $this->prepareValue($replValue);
                    $testQuery = $this->replaceMarker($testQuery, $key, $replValue);
                }
            }
            $this->fullQuery = $testQuery;

            return $testQuery;
        }

        /**
         * @param $queryString
         * @param $marker
         * @param $replValue
         *
         * @return mixed
         */
        private function replaceMarker($queryString, $marker, $replValue) // TODO this should be using PDOs bind function not a regex substitution
        {
            /**
             * UPDATE - Issue #3
             * It is acceptable for bound parameters to be provided without the leading :, so if we are not matching
             * a ?, we want to check for the presence of the leading : and add it if it is not there.
             */
            if (is_numeric($marker)) {
                $marker = "\?";
            } else {
                $marker = (preg_match("/^:/", $marker)) ? $marker : ":" . $marker;
            }
            $testParam = "/" . $marker . "(?!\w)/";
            return preg_replace($testParam, $replValue, $queryString, 1);
        }

        /**
         * Overrides the default \PDOStatement method to generate the full query string - then accesses and returns
         * parent::execute method
         *
         * @param array $inputParams
         *
         * @return bool - default of \PDOStatement::execute()
         */
        public function execute($inputParams = null)
        {
            $this->interpolateQuery($inputParams);

            return parent::execute($inputParams);
        }

        /**
         * Prepares values for insertion into the resultant query string - if $this->_pdo is a valid PDO object, we'll use
         * that PDO driver's quote method to prepare the query value. Otherwise:
         *
         *      addslashes is not suitable for production logging, etc. You can update this method to perform the necessary
         *      escaping translations for your datasource driver. Please consider updating your processes to provide a valid
         *      PDO object that can perform the necessary translations and can be updated with your e.g. package management,
         *      etc.
         *
         * @param string[] $value - the value to be prepared for injection as a value in the query string
         *
         * @return string $value - prepared $value
         */
        private function prepareValue($value)
        {
            if (!$this->_pdo) {
                return "'" . addslashes($value['value']) . "'";
            }
            if (\PDO::PARAM_INT === $value['datatype']) {
                return (int) $value['value'];
            }
            return  $this->_pdo->quote($value['value']);
        }
    }
}
