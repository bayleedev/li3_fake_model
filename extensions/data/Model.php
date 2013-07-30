<?php

namespace li3_fake_model\extensions\data;

use li3_fake_model\extensions\data\source\FakeMongoDb;

use lithium\data\Connections;
use lithium\data\model\Query;
use lithium\util\Inflector;
use lithium\data\entity\Document;

class Model {

	// Primary key identifier
	public $primaryKey = '_id';

	// Source (table/collection) name
	// leave as null to infer from class name
	static public $sourceName = null;

	// Connection name
	static public $connectionName = 'default';

	// store cached copy of connection
	static protected $cachedConnection = null;

	/* Raw data.
	 * Use the accessor methods instead for full funcitonality.
	 */
	public $data = array();

	/* Constructor - creates new instance of model object.
	 * Note: does not save to the database.
	 *
	 * @param array $data - data to store in database
	 */
	public function __construct($data=array()) {
		$this->data = $data;
	}

	/* Returns boolean true if this record already exists in the database
	 */
	public function exists() {
		return !is_null($this->{$this->primaryKey});
	}

	/* Saves model data to the database.
	 */
	public function save() {
		$type = $this->exists() ? 'update' : 'create';
		$doc = new Document();
		$doc->set($this->data);
		$query = new Query(array(
			'entity' => $doc,
			'model' => get_class($this),
			'conditions' => array(
				$this->primaryKey => $this->{$this->primaryKey},
			),
		));
		$db = static::connection();
		$result = $db->{$type}($query);
		$exported = $doc->export();
		$this->data[$this->primaryKey] = $exported['update'][$this->primaryKey];
		return $result;
	}

	/* Returns the specific data property as if it were an actual top-level property.
	 *
	 * Alternatively, you can just use `$model->data[$prop]`
	 */
	public function __get($prop) {
		if(isset($this->data[$prop])) {
			return $this->data[$prop];
		}
	}

	/* Sets the specified data property.
	 *
	 * Alternatively, you can just use `$model->data[$prop] = $val`
	 */
	public function __set($prop, $val) {
		return $this->data[$prop] = $val;
	}

	/* Create a new model object.
	 * Really just an alias for `new Model()`
	 *
	 * Note: Does not save to the database.
	 *
	 * @param array $data
	 */
	static public function create($data=array()) {
		return new static($data);
	}

	/* Query all records from the database
	 * and return as an array.
	 *
	 * @param array $conditions
	 * @param array $options
	 */
	static public function all($conditions=array(), $options=array()) {
		$query = new Query(array(
			'model' => get_called_class(),
			'conditions' => $conditions
		));
		$db = static::connection();
		$results = $db->read($query);
		$records = array();
		foreach($results as $result) {
			$records[] = new static($result);
		}
		return $records;
	}

	/* Query a single record from the database
	 * and return model instance.
	 *
	 * @param array $conditions
	 * @param array $options
	 */
	static public function first($conditions=array(), $options=array()) {
		$query = new Query(array(
			'model' => get_called_class(),
			'conditions' => $conditions
		));
		$db = static::connection();
		$results = $db->read($query);
		if(count($results) > 0) {
			return new static($results[0]);
		}
	}

	/* Return meta information, for compatibility with LI3.
	 *
	 * @param string $key - name of property to return, e.g.
	 *                      'name' or 'source'
	 * @param string $val - ignored
	 */
	static public function meta($key=null, $val=null) {
		$class = get_called_class();
		$parts = explode("\\", $class);
		$name = $parts[count($parts)-1];
		if($key == 'name') {
			return $name;
		} else if($key == 'source') {
			return static::$sourceName ? static::$sourceName : Inflector::tableize($name);
		}
	}

	/* Returns an empty schema array.
	 *
	 * We don't support schema, but LI3 Query still looks for it.
	 */
	static public function schema() {
		return array();
	}

	/* Fetch and return the LI3 database connection named in
	 * static $connectionName, wrapped in our own fake connection
	 * adapter :-)
	 */
	static public function connection() {
		if(!isset(static::$cachedConnection)) {
			$conn = Connections::get(static::$connectionName);
			$connClass = get_class($conn);
			if(preg_match('/MongoDb/', $connClass)) {
				static::$cachedConnection = new FakeMongoDb($conn);
			} else {
				throw 'not yet implemented';
			}
		}
		return static::$cachedConnection;
	}

}