<?php

/**
 *
 */
namespace util
{
    /**
     * Class collection
     * @package util
     */
    class collection
    {
        /**
         * @param \ArrayObject $collection
         * @param int          $offset
         * @param int          $quantity
         *
         * @return array|\ArrayObject
         */
        public static function collection_slice($collection, $offset, $quantity)
        {
            $copyCollection = clone $collection;
            $copyCollection->exchangeArray([]);

            foreach (range($offset, $quantity + $offset - 1) as $index) {
                if (array_key_exists($index, $collection)) {
                    $copyCollection[] = $collection[$index];
                }
            }

            return $copyCollection;
        }

        /**
         * @param \ArrayObject $collection1
         * @param \ArrayObject $collection2
         *
         * @return array|\ArrayObject
         *
         * @throws \Exception
         */
        public static function collection_append($collection1, $collection2)
        {
            if (get_class($collection1) != get_class($collection2)) {
                throw new \Exception(get_class($collection1) . " objects cannot be merged with "
                    . get_class($collection2) . " objects");
            }

            foreach ($collection2 as $collectionItem) {
                $collection1[] = $collectionItem;
            }

            return $collection1;
        }

        /** <<< --- ARRAY Function --- >>>> */
        /**
         * Returns only a numeric array.
         *
         * @param \ArrayObject $arr
         *
         * @return array|\ArrayObject
         */
        public static function getNumericArray($arr)
        {
            $numericArray = [];

            foreach ($arr as $arrItem) {
                if (is_numeric($arrItem)) {
                    $numericArray[] = (int) $arrItem;
                }
            }

            return $numericArray;
        }

        /**
         * @param \ArrayObject $array
         * @param int          $offset
         * @param int          $quantity
         *
         * @return array|\ArrayObject
         */
        public static function array_slice($array, $offset, $quantity)
        {
            $result = [];

            for ($x = 0; $x < $quantity; $x++) {
                $result[] = $array[$offset + $x];
            }
            return $result;
        }

        /**
         * @param string        $key
         * @param \ArrayObject  $array
         *
         * @return array|\ArrayObject
         */
        public static function array_pluck($key, $array)
        {
            $result = [];
            foreach ($array as $item) {
                $result[]=$item->{$key};
            }
            return $result;
        }

        /**
         * @param \ArrayObject      $array
         * @param string|int        $parameter
         * @param string|int        $expectedValue
         *
         * @return array|\ArrayObject
         */
        public static function array_select($array, $parameter, $expectedValue)
        {
            $result = [];
            foreach ($array as $item) {
                if ($item->{$parameter} == $expectedValue) {
                    $result[]=$item;
                }
            }
            return $result;
        }

    }
}