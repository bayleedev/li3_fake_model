<?php

namespace li3_fake_model\extensions\data\source;

class FakeMongoDb extends \lithium\data\source\MongoDb {

	public function __construct($connection) {
		parent::__construct(array('autoConnect' => false));
		$this->connection = $connection->connection;
		$this->_isConnected = true;
	}

	/* A much simpler item instantiation...
	 * just returns the raw data!
	 */
	public function item($model, array $data = array(), array $options = array()) {
		$data = array();
		// WTF foreach doesn't work on this iterator???
		while($record = $options['result']->next()) {
			$data[] = $record;
		}
		return $data;
	}

}