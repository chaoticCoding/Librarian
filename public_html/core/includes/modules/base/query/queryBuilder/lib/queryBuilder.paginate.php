<?php

/**
 *
 */
namespace core\queryBuilder
{
    /**
     * Class queryBuilder
     * @package core
     */
    class pagination extends \core\query\queryAbstract
    {

        /** @var \int */
        private $_count;

        /** @var \int */
        private $_currentPage;

        /** @var \int */
        private $_totalPages;

        /** @var \int */
        private $_previousPage;

        /** @var \int */
        private $_nextPage;

        /**
         * @param int $pageSize
         * @param int $pageStart
         *
         * @return mixed
         */
        public function getPage ($pageSize = 5, $pageStart = 0)
        {
            $this->paginate($pageSize, $pageStart);
            $query = $this->executeSelectQuery();
            $this->setupPagination($pageSize, $pageStart);

            return $query;
        }

        /**
         * @param $pageSize
         * @param $pageStart
         */
        private function paginate ($pageSize, $pageStart)
        {
            $this->loadCountSelectQuery();

            $this->limit($pageSize, $pageStart);
        }

        /**
         * @param int $pageSize
         * @param int $pageStart
         */
        public function loadPage ($pageSize = 5, $pageStart = 0)
        {
            $this->paginate($pageSize, $pageStart);
            $this->executeSelectQuery();
            $this->setupPagination($pageSize, $pageStart);
        }

        /**
         * @param $pageSize
         * @param $pageStart
         */
        private function setupPagination ($pageSize, $pageStart)
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
        public function getCurrentPage ()
        {
            return $this->_currentPage;
        }

        /**
         * @return mixed
         */
        public function getTotalPages ()
        {
            return $this->_totalPages;
        }

        /**
         * @return mixed
         */
        public function getPreviousPage ()
        {
            return $this->_previousPage;
        }

        /**
         * @return mixed
         */
        public function getNextPage ()
        {
            return $this->_nextPage;
        }

        /**
         * @return mixed
         */
        public function getTotalResults ()
        {
            return $this->_count;
        }

        /**
         * @param $totalResults
         * @param $currentPage
         * @param $pageSize
         * @param $pageLimit
         */
        private function setPages ($totalResults, $currentPage, $pageSize, $pageLimit)
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
        public function retrievePage ($pageSize = 10, $page = 1, $pageLimit = 10)
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
            $results = [];

            // load array
            while ($offset < $endIndex) {
                if (array_key_exists($offset, $this)) {
                    $results[] = $this[ $offset ];
                } else {
                    break;
                }
                $offset++;
            }

            $this->exchangeArray($results);
        }
    }
}