<?php

/**
 *
 */
namespace core\base\collection
{
    /** TODO convert to using querybuilder/filter pattern
     * Class collection_item
     * @package core\base
     */
    abstract class _item extends \ArrayObject
    {
        /** @var string : table name */
        protected $_loadFrom;

        /** @var string : table name */
        protected $_update;

        /** @var string : table name */
        protected $_insertInto;

        /** @var string : table name */
        protected $_deleteFrom;

        /** @var string */
        protected $_primaryKey;

        /** @var bool */
        protected $_logMetrics;

        /** @var bool */
        protected $_logAuditTrail;

        /** @var \collection_collection_item */
        private $_original;

        /** @var bool */
        protected $_useCache;

        /**
         *
         *
         * @param null $table
         * @param string $primaryKey
         * @param bool $logAuditTrail
         * @param bool $logMetrics
         * @param bool $useCache
         */
        public function __construct($table = null, $primaryKey = '', $logAuditTrail = true, $logMetrics = false, $useCache = false)
        {
            $this->_loadFrom = $table;
            $this->_update = $table;
            $this->_insertInto = $table;
            $this->_deleteFrom = $table;
            $this->_primaryKey = strtolower($primaryKey);
            $this->_logMetrics = $logMetrics;
            $this->_logAuditTrail = $logAuditTrail;
            $this->_useCache = $useCache;
        }

        /**
         * @param $name
         * @param $value
         *
         * @throws \Exception
         */
        public function __set($name, $value)
        {
            $name = strtolower($name);

            switch ($name) {

                default:
                    if (array_key_exists($name, $this)) {
                        $this[$name] = $value;
                    } else {
                        throw new InvalidArgumentException("Parameter cannot be set: {$name}");
                    }

                    break;
            }
        }

        /**
         * @param $name
         *
         * @return mixed
         * @throws \Exception
         */
        public function __get($name)
        {
            $name = strToLower($name);

            switch ($name) {

                default:
                    if (array_key_exists($name, $this)) {
                        return $this[$name];
                    }
                    throw new InvalidArgumentException("No such parameter: {$name}");

                    break;
            }
        }

        /**
         * @throws \Exception
         */
        public function loadFields()
        {
            $query = datasource::get(DATASOURCE_ID)->prepare("DESCRIBE {$this->_loadFrom} ");

            if ($query->execute()) {
                while ($record = $query->fetch(PDO::FETCH_ASSOC)) {
                    $this[strtolower($record['Field'])] = '';
                }
            } else {
                $this->logPreparedStatement($query);
                syslog(LOG_ERR, 'SQL Error when loading class fields: ' . print_r($query->errorInfo(), true));
                throw new Exception('SQL Error when loading class fields');
            }
        }

        #####################################################################
        # Load by primary key
        #####################################################################
        /** TODO convert to Query Object
         * @param      $value
         * @param bool $bypassCache
         *
         * @return $this
         * @throws \Exception
         */
        public function load($value, $bypassCache = false)
        {
            $cacheKey = cache::generateCacheKeyFromClass($this, $value);

            if ($this->_useCache && !$bypassCache && $this->tryToLoadFromCache($cacheKey)) {
                return $this;
            }

            $query = datasource::get(DATASOURCE_ID)->prepare(
                "
			SELECT *
            FROM {$this->_loadFrom}
			WHERE {$this->_primaryKey} = :{$this->_primaryKey}
		");

            $query->bindValue(":{$this->_primaryKey}", $value);

            if ($query->execute()) {
                if (LOG_DATABASE_QUERIES === true) {
                    $this->logPreparedStatement($query);
                }

                if ($record = $query->fetch(PDO::FETCH_ASSOC)) {
                    $this->import($record);
                }
            } else {
                $this->logPreparedStatement($query);
                syslog(LOG_ERR, "SQL Error when loading from ID: " . print_r($query->errorInfo(), TRUE));
                throw new Exception('SQL Error when loading from that ID');
            }

            if ($this->_useCache) {
                cache::cacheItem($this, $value);
            }


            return $this;
        }

        #####################################################################
        # save
        #####################################################################
        /**
         * @throws \Exception
         */
        public function save()
        {
            $fields = '';
            $values = '';
            $updateText = '';

            foreach ($this as $key => $value) {
                if ($value) { //if inserting and no value, dont add the column
                    $fields .= "`{$key}`, ";
                    $values .= ":{$key}, ";
                }

                if (!($key == 'id' && !$value)) {
                    $updateText .= "`{$key}` = :{$key}_update, "; //cannot reuse name twice
                } elseif ($key == 'id' && !$value) {
                    $updateText .= "`{$key}` = LAST_INSERT_ID(id), "; // need this to correctly update last inserted id
                }
            }

            $fields = rtrim($fields, ', ');
            $values = rtrim($values, ', ');
            $updateText = rtrim($updateText, ', ');

            // TODO Controls for how to handle update
            $datasource = datasource::get(DATASOURCE_ID);
            $query = $datasource->prepare("
			INSERT INTO {$this->_insertInto} (
				{$fields}
			) VALUES (
				{$values}
			)

            ON DUPLICATE KEY

			UPDATE {$updateText}
		");

            foreach ($this as $key => $value) {
                if ($value) { //if inserting and no value, dont add the column
                    $query->bindValue(":{$key}", $value);
                }

                if (!($key == 'id' && !$value)) {
                    $query->bindValue(":{$key}_update", $value);
                }
            }

            if ($query->execute()) {
                if (LOG_DATABASE_QUERIES === true) {
                    $this->logPreparedStatement($query);
                }

                if ($this->id) {
                    $this->logChangeEvents(access::EVENT_TYPE_EDIT);
                    $this->load($this->id, true);

                } else {
                    $this->load($datasource->getLastInsertedId(), true);
                    $this->logChangeEvents(access::EVENT_TYPE_NEW);

                    if ($this->_logMetrics) {
                        $eventType = "created " . get_class($this);
                        $eventData = array("id" => $this->id);

                        logging::queueEvent($eventType, $eventData);
                    }
                }

                cache::invalidateCollectionsThatContainItem($this);
            } else {
                $this->logPreparedStatement($query);
                syslog(LOG_ERR, "SQL Error when inserting: " . print_r($query->errorInfo(), true));
                throw new Exception('SQL Error when inserting.');
            }
        }

        #####################################################################
        # Delete
        #####################################################################
        /**
         * @throws \Exception
         */
        public function delete()
        {
            $query = datasource::get(DATASOURCE_ID)->prepare("
			DELETE FROM {$this->_deleteFrom}
			WHERE `{$this->_primaryKey}` = :{$this->_primaryKey}
		");

            $query->bindValue(":{$this->_primaryKey}", $this[$this->_primaryKey]);

            if ($query->execute()) {
                if (LOG_DATABASE_QUERIES === true) {
                    $this->logPreparedStatement($query);
                }
            } else {
                $this->logPreparedStatement($query);
                syslog(LOG_ERR, "SQL Error unable to delete with that primary key id: " . print_r($query->errorInfo(), true));
                throw new Exception('SQL Error unable to delete with that primary key id.');


            }

            $this->logChangeEvents(access::EVENT_TYPE_DELETE);
        }

        /**
         * @param $eventType
         */
        private function logChangeEvents($eventType)
        {
            if (!$this->_logAuditTrail) {
                return;
            }

            $object = get_class($this);
            $eventId = hash("sha256", uniqid($object));

            $userId = null;

            if (user::isSignedIn()) {
                $userId = user::getOriginalUser()->id;
            }

            switch ($eventType) {
                case access::EVENT_TYPE_EDIT:
                    $this->saveObjectChangeLogs($userId, $eventType, $eventId, $object);
                    break;
                case access::EVENT_TYPE_NEW:
                case access::EVENT_TYPE_DELETE:
                    $this->saveObjectLog($userId, $eventType, $eventId, $object);
                    break;
            }
        }

        /**
         * @param $userId
         * @param $eventType
         * @param $eventId
         * @param $object
         */
        private function saveObjectChangeLogs($userId, $eventType, $eventId, $object)
        {
            if (!$this->_original) {
                return;
            }

            $serializedoriginal = $this->_original->serialize();
            $serializedoriginalHash = md5($serializedoriginal);

            $serializedME = $this->serialize();
            $serializedMEHash = md5($serializedME);

            if ($serializedoriginalHash != $serializedMEHash) {

                $log = access::getLogItem();
                $log->userId = $userId;
                $log->ipAddress = \core\environment\client::getClientIPAdress();
                $log->event = $eventType;
                $log->eventId = $eventId;
                $log->object = $object;
                $log->objectId = $this->_original->id;
                $log->newHash = $serializedoriginalHash;
                $log->originalValue = $serializedoriginal;
                $log->newValue = $serializedMEHash;
                $log->save();
            }
        }

        /**
         * @param $userId
         * @param $eventType
         * @param $eventId
         * @param $object
         */
        private function saveObjectLog($userId, $eventType, $eventId, $object)
        {
            $log = access::getLogItem();
            $log->userId = $userId;
            $log->ipAddress = \core\environment\client::getClientIPAdress();
            $log->event = $eventType;
            $log->eventId = $eventId;
            $log->object = $object;
            $log->objectId = $this->id;
            $log->save();
        }

        /**
         * @param $data
         *
         * @throws \Exception
         */
        public function import($data)
        {
            if (is_array($data)) {
                foreach ($data as $key => $value) {
                    $this[strToLower($key)] = $value;
                }

                $this->setOriginalObject();
            } else {
                throw new Exception('Unable to import data from unrecognized source.');
            }
        }


        /**
         *
         */
        private function setOriginalObject()
        {
            if (!$this->_logAuditTrail) {
                return;
            }

            $this->_original = clone $this;
        }

        /**
         * @param $cacheKey
         *
         * @return bool
         */
        public function tryToLoadFromCache($cacheKey)
        {
            /** @var \company_collection_item $object */
            $object = cache::get($cacheKey);

            if ($object) {
                foreach ($object as $offset => $value) {
                    $this[$offset] = $value;
                }

                return true;
            }

            return false;
        }

        /**
         * @return mixed
         */
        public function getPrimaryKeyValue()
        {
            if (isset($this[$this->_primaryKey])) {
                return $this[$this->_primaryKey];
            }
        }

        /**
         * @return string
         */
        public function getTableName()
        {
            return $this->_loadFrom;
        }

        /**
         * @param \PDOStatement $query
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