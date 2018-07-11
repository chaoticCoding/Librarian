<?php

/**
 * Trait commonFilterDeleted
 */
trait filterDeleted
{
    private $__where_Deleted    = '(user_collection.deleted IS NOT NULL AND user_collection.deleted >= 0 AND user_collection.deleted >= "0000-00-00 00:00:00")';
    private $__where_notDeleted = '(user_collection.deleted IS NULL or user_collection.deleted = 0 or user_collection.deleted = "0000-00-00 00:00:00")';

    private $__where_All = "";
    /**
     *
     */
    private function getFilter_Deleted(filter_collection $filters)
    {
        $filter = null;
        if ($filters->exists('deleted')) {

            $deleted = $filters->get('deleted')->value;
            switch ($deleted) {
                case 0:
                    $filter = $this->__where_notDeleted;
                    break;
                case 1:
                    $filter = $this->__where_Deleted;
                    break;

                case 'all':
                default:
                    //$this->customWhere('(user_collection.deleted IS NOT NULL AND user_collection.deleted >= 0 AND user_collection.deleted >= "0000-00-00 00:00:00")');
            }

        } else {
            if (!$filters->exists('all')) {
                $filter = $this->__where_notDeleted;
            }
        }

        return $filter;
    }
}