<?php

require_once 'lib/request_item.class.php';

/**
 * Class request
 *
 *  A wrapper for PHPs standard $_REQUEST global.  Works with both single
 *  and array values.
 *
 *
 *  (Bitwise) options...
 *
 *  	PROHIBIT_AUTO_CREATE:  $_REQUEST elements are automatically created
 *  	if they don't already exist.  This suppresses that behavior & throws
 *  	an exception instead.
 *
 *  	AUTO_TRIM_VALUE: Trim() the source value.
 *
 *
 *  Simple access example...
 *
 *  	print request::get('myVar');
 *
 *
 *  Simple access w/ options...
 *
 *  	print request::get('myVar', cmsRequest::PROHIBIT_AUTO_CREATE | cmsRequest::AUTO_TRIM_VALUE);
 *
 *
 *  Get value cast to (int)...
 *
 * 	print request::get('myVar')->getValueAsInt();
 *
 *
 *  Get HTML encoded value...
 *
 * 	print request::get('myVar')->getValueAsString('special_chars');
 *
 *
 *  Set a $_REQUEST value...
 *
 *  	request::get('myVar')->setValue('<b>Some</b> value');
 *
 *
 *  Sanitize variable output (uses PHPs filter functions)...
 *
 *  	print request::get('myVar')->getSanitizedValue('full_special_chars');
 *
 *
 *  Test source variable value using cmsDataTest lib...
 *
 * 	if( ! request::get('myVar')->dataTest('isEmail') ) print 'Not an email address.  Bummer.';
 */
class request
{
    /**  */
    const PROHIBIT_AUTO_CREATE = 1;

    /**  */
    const AUTO_TRIM_VALUE = 2;

    /** @var int  */
    private static $globalOptions = 0;

    /**
     * Set global options
     *
     * @param $options
     */
    public static function setGlobalOptions($options)
    {
        self::$globalOptions = $options;
    }


    /**
     * Get new item
     *
     * @return \request_item
     */
    public static function getNewItem()
    {
        return new request_item();
    }

    /**
     * Get named item
     *
     * @param      $key
     * @param null $options
     *
     * @return \request_item
     */
    public static function get($key, $options = null)
    {
        $options = is_null($options) ? self::$globalOptions : $options;

        $item = self::getNewItem();

        $item->setOptions($options);

        $item->attach($key);

        return $item;
    }
}
