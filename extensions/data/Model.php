<?php

namespace li3_fake_model\extensions\data;

use lithium\data\Connections;
use lithium\data\model\Query;
use lithium\util\Inflector;
use lithium\data\entity\Document;
use lithium\core\ConfigException;

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

	/**
	 * Defined relationships.
	 *
	 * @var array
	 */
	static public $relationships = array('hasMany', 'hasOne');

	public $hasMany = array();

	public $hasOne = array();

	/**
	 * Relationship classes
	 *
	 * @var array
	 */
	public static $classes = array(
		'hasMany' => 'li3_fake_model\extensions\data\relationships\HasMany',
		'hasOne' => 'li3_fake_model\extensions\data\relationships\HasOne',
		'database' => 'li3_fake_model\extensions\data\source\FakeMongoDb',
	);

	/**
	 * Stores model instances for internal use.
	 *
	 * While the `Model` public API does not require instantiation thanks to late static binding
	 * introduced in PHP 5.3, LSB does not apply to class attributes. In order to prevent you
	 * from needing to redeclare every single `Model` class attribute in subclasses, instances of
	 * the models are stored and used internally.
	 *
	 * @var array
	 */
	protected static $_instances = array();

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
		$options += array(
			'with' => array(),
		);
		$with = $options['with'];
		unset($options['with']);

		$query = new Query($options + array(
			'model' => get_called_class(),
			'conditions' => $conditions,
		));
		$db = static::connection();
		$results = $db->read($query);
		$records = array();
		foreach($results as $result) {
			$records[] = new static($result);
		}
		return static::relationships($records, $with);
	}

	/* Query a single record from the database
	 * and return model instance.
	 *
	 * @param array $conditions
	 * @param array $options
	 */
	static public function first($conditions=array(), $options=array()) {
		$options += array(
			'with' => array(),
		);
		$with = $options['with'];
		unset($options['with']);

		$query = new Query($options + array(
			'model' => get_called_class(),
			'conditions' => $conditions
		));
		$db = static::connection();
		$results = $db->read($query);
		if(count($results) > 0) {
			$results = static::relationships(array(new static($results[0])), $with);
			return $results[0];
		}
	}

	// setup relationships
	static public function relationships($results, $with) {
		$first = $results[0];
		foreach ($with as $key => $value) {
			$relationshipInfo = static::_determineChildInfo($key, $value);
			$relationship = $first->retrieveRelationship($relationshipInfo['name']);
			$relationship->with($relationshipInfo['with']);
			$relationship->data($results);
			$relationship->appendData();
			$results = $relationship->data();
		}
		return $results;
	}

	/**
	 * Will determine if `$key` is the relationship name, or numeric.
	 * If this is the name, the value is the child's `with` statement.
	 *
	 * @return [type] [description]
	 */
	protected static function _determineChildInfo($key, $value) {
		if (is_array($value)) {
			return array(
				'name' => $key,
				'with' => $value,
			);
		}
		return array(
			'name' => $value,
			'with' => array(),
		);
	}

	/**
	 * Retrusn a given relationship or throws `lithium\core\ConfigException`.
	 *
	 * @param  string $name
	 * @return string
	 */
	public function retrieveRelationship($name) {
		if (strrpos($name, '\\') !== false) {
			$name = substr($name, strrpos($name, '\\') + 1);
		}
		foreach(static::$relationships as $type) {
			if (!empty($this->{$type}) && isset($this->{$type}[$name])) {
				return new static::$classes[$type]($this->{$type}[$name]);
			}
		}
		throw new ConfigException('No relationship ' . $name . ' found in ' . get_called_class());
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
				$db = static::$classes['database'];
				static::$cachedConnection = new $db($conn);
			} else {
				throw 'not yet implemented';
			}
		}
		return static::$cachedConnection;
	}

	/**
	 * Returns a list of models related to `Model`, or a list of models related
	 * to this model, but of a certain type.
	 *
	 * If a relationship type is given, all of those relationships are returned.
	 * If a model name is given, that relationship is returned.
	 *
	 * @param string $name Name of the model, or relation. Like 'hasMany' or 'MockPost'.
	 * @return array An array of relation types.
	 */
	public static function relations($name = null) {
		$self = static::_object();

		if (isset(static::$relationships[$name])) {
			return $self->{$name};
		}

		foreach (static::$relationships as $relationship) {
			foreach ($self->{$relationship} as $key => $rel) {
				if (in_array($name, array($key, $rel['to']))) {
					return array(
						'type' => $relationship,
						'data' => $self->{$relationship}[$key],
					);
				}
			}
		}

		return false;
	}

	/**
	 * Will return a cached instance of the given class.
	 *
	 * @return object
	 */
	protected static function _object() {
		$class = get_called_class();

		if (!isset(static::$_instances[$class])) {
			static::$_instances[$class] = new $class();
		}
		return static::$_instances[$class];
	}

}