<?php

require_once 'lib/filter/filter_collection.class.php';
require_once 'lib/filter/filter_collection_item.class.php';

/**
 * Class common
 */
class filter extends collection_factory
{
    /**
     * @return \filter_collection
     */
    public static function getFilterColl()
    {
        return new filter_collection();
    }

    /**
     * @return \filter_collection_item
     */
    public static function getFilterItem()
    {
        return new filter_collection_item();
    }

    /**
     * @param \string $name
     * @param \string|\string[]|\int|\int[]|\bool $value
     *
     * @return \filter_collection_item
     */
    public static function createFilterItem( $name, $value)
    {
        $filter = self::getFilterItem();
        $filter->name = $name;
        $filter->value = $value;

        return $filter;
    }
}
