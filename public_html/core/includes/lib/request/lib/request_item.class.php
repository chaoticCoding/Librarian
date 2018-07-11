<?php

/**
 * @property array|bool|string value
 */
class request_item
{
    /** @var int  */
    protected $_options = 0;

    /** @var \string  */
    protected $_key;

    /** @var   */
    protected $_request_ptr;

    ######################################################################
    # Getters
    ######################################################################
    /**
     * @param $name
     *
     * @return array|bool|string
     *
     * @throws \Exception
     */
    public function __get($name)
    {
        switch (strToLower($name)) {

            case 'prohibit_auto_create':
                return ($this->_options & request::PROHIBIT_AUTO_CREATE) > 0 ? true : false;

            case 'auto_trim_value':
                return ($this->_options & request::AUTO_TRIM_VALUE) > 0 ? true : false;

            case 'value':
                return $this->getValue();

            default:
                throw new Exception("No such property: {$name}");

        }
    }

    ######################################################################
    # Setters
    ######################################################################
    /**
     * @param $name
     * @param $value
     *
     * @throws \Exception
     */
    public function __set($name, $value)
    {
        switch (strToLower($name)) {

            case 'value':

                $this->setValue($value);

                break;

            default:

                throw new Exception("Property cannot be set: {$name}");

        }
    }

    ######################################################################
    # To string
    ######################################################################
    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getValue();
    }

    ######################################################################
    # Set options
    ######################################################################
    /**
     * @param $options
     */
    public function setOptions($options)
    {
        $this->_options = $options;
    }

    ######################################################################
    # Attach to $_REQUEST item
    ######################################################################
    /**
     * @param $key
     *
     * @throws \Exception
     */
    public function attach($key)
    {

        if (array_key_exists($key, $_REQUEST)) {
            $this->_key = $key;

            $this->_request_ptr =& $_REQUEST[$this->_key];

        } elseif ($this->prohibit_auto_create === false) {
            $this->_key = $key;

            $_REQUEST[$this->_key] = null;

            $this->_request_ptr =& $_REQUEST[$this->_key];
        } else {
            throw new Exception("Invalid key: {$key}");
        }
    }

    ######################################################################
    # Cast each array value to $type
    ######################################################################
    /**
     * @param $array
     * @param $type
     *
     * @return array
     * @throws \Exception
     */
    protected function castArray($array, $type)
    {
        $result = array();

        switch ($type) {

            case 'bool':
                foreach ($array as $val) {
                    $result[] =  (bool) $val;
                }

                return $result;

            case 'float':
                foreach ($array as $val) {
                    $result[] =  (float) $val;
                }

                return $result;

            case 'int':
                foreach ($array as $val) {
                    $result[] =  (int) $val;
                }

                return $result;

            case 'string':
                foreach ($array as $val) {
                    $result[] =  (string) $val;
                }

                return $result;

            default:
                throw new Exception("Invalid cast type: {$type}");

        }
    }

    ######################################################################
    # Get value
    ######################################################################
    /**
     * @return array|string
     */
    public function getValue()
    {
        $val = $this->_request_ptr;

        if ($this->auto_trim_value) {
            if (is_array($val)) {
                $countVal = count($val);
                for ($x = 0; $x < $countVal; $x++) {
                    $val[$x] = trim($val[$x]);
                }

                return $val;
            } else {
                return trim($val);
            }
        } else {
            return $val;
        }
    }

    ######################################################################
    # Set value
    ######################################################################
    /**
     * @param $value
     */
    public function setValue($value)
    {
        $this->_request_ptr = $value;
    }

    ######################################################################
    # Get value cast to (bool)
    ######################################################################
    /**
     * @return array|bool
     */
    public function getValueAsBool()
    {
        $val = $this->getValue();

        return is_array($val) ? $this->castArray($val, 'bool') : (bool) $val;
    }

    ######################################################################
    # Get value cast to (float)
    ######################################################################
    /**
     * @return array|float
     */
    public function getValueAsFloat()
    {
        $val = $this->getValue();

        return is_array($val) ? $this->castArray($val, 'float') : (float) $val;
    }

    ######################################################################
    # Get value cast to (int)
    ######################################################################
    /**
     * @return array|int
     */
    public function getValueAsInt()
    {
        $val = $this->getValue();

        return is_array($val) ? $this->castArray($val, 'int') : (int) $val;
    }

    ######################################################################
    # Get value cast to (string) w/ optional php filter spec
    # Use getValueAsString('special_chars') for HTML encoded values.
    # For other filters and optional flags, see:
    #	http://www.php.net/manual/en/filter.filters.sanitize.php
    ######################################################################
    /**
     * @param null $filter
     * @param int  $options
     *
     * @return array|string
     */
    public function getValueAsString($filter = null, $options = 0)
    {
        $val = is_null($filter) ? $this->getValue() : $this->getSanitizedValue($filter, $options);

        return is_array($val) ? $this->castArray($val, 'string') : (string) $val;
    }

    ######################################################################
    # Get mapped value
    # If value equals $test, returns $whenTrue otherwise $whenFalse is
    # returned.
    # Example: getMappedValue('yes', 'You chose yes', 'You chose no')
    ######################################################################
    /**
     * @param      $test
     * @param bool $whenTrue
     * @param bool $whenFalse
     *
     * @return bool
     */
    public function getMappedValue($test, $whenTrue = true, $whenFalse = false)
    {
        $val = $this->getValue();

        if (is_array($val)) {
            if (! is_array($test)) {
                $test = array($test);
            }

            return count(array_diff($test, $val)) == 0 ? $whenTrue : $whenFalse;
        } else {
            return $val == $test ? $whenTrue : $whenFalse;
        }
    }

    ######################################################################
    # Get sanitized value
    # Return value after running it through filter_var().
    # Example: getSanitizedValue('special_chars')
    # For filters and optional flags, see:
    #	http://www.php.net/manual/en/filter.filters.sanitize.php
    ######################################################################
    /**
     * @param     $filter
     * @param int $options
     *
     * @return array|mixed|string
     */
    public function getSanitizedValue($filter, $options = 0)
    {
        $val = $this->getValue();

        if (is_array($val)) {
            $countVal = count($val);
            for ($x = 0; $x < $countVal; $x++) {
                $val[$x] = $this->filterValue($val[$x], $filter, $options);
            }
        } else {
            $val = $this->filterValue($val, $filter, $options);
        }

        return $val;
    }

    ######################################################################
    # Filter value
    ######################################################################
    /**
     * @param $value
     * @param $filter
     * @param $options
     *
     * @return mixed
     * @throws \Exception
     */
    protected function filterValue($value, $filter, $options)
    {
        if (in_array($filter, filter_list())) {
            $id = filter_id($filter);

            return filter_var($value, $id, $options);
        } else {
            throw new Exception("Invalid sanitize filter name: {$filter}");
        }
    }
}
