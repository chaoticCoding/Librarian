<?php

/**
 * Class filter_collection
 */
class filter_collection extends ArrayObject
{
    /**
     * filter_collection constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function exists($name, $nullOnly = false)
    {
        foreach ($this as $filter) {

            if($nullOnly === true){
                if ($filter->name == $name && (null !== $filter->value)) {
                    return true;

                }
            } else {
                if ($filter->name == $name && ($filter->value || $filter->value === '0')) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $name
     *
     * @return mixed|null
     */
    public function get($name)
    {
        foreach ($this as $filter) {
            if ($filter->name == $name) {
                return $filter;
            }
        }

        return null;
    }

    /**
     * @param \string $name
     * @param \string|\string[]|\int|\int[]|\bool $value
     */
    public function filterBy($name, $value)
    {
        $this->append(
            \common::createFilterItem(
                                        $name,
                                        $value
            )
        );
    }
}
