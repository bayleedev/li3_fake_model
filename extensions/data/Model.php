<?php

namespace li3_fake_model\extensions\data;

use lithium\data\Connections;
use lithium\util\Inflector;
use lithium\data\entity\Document;
use lithium\core\ConfigException;
use li3_fake_model\extensions\data\model\Query;

class Model {

	// Primary key identifier
	public $primaryKey = '_id';

	// Source (table/collection) name
	// leave as null to infer from class name
	public static $sourceName = null;

	// Connection name
	public static $connectionName = 'default';

	// store cached copy of connection
	static protected $cachedConnection = null;

	/**
	 * Raw data.
	 * Use the accessor methods instead for full funcitonality.
	 *
	 * @var  array
	 */
	public $data = array();

	/**
	 * Relation data.
	 * Use the accessor methods instead for full funcitonality.
	 *
	 * @var  array
	 */
	public $relData = array();

	/**
	 * Defined relationships.
	 *
	 * @var array
	 */
	public static $relationships = array('hasMany', 'hasOne');

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

	/**
	 * Constructor - creates new instance of model object.
	 * Note: does not save to the database.
	 *
	 * @param array $data - data to store in database
	 */
	public function __construct($data=array()) {
		$this->data = $data;
	}

	/**
	 * Returns boolean true if this record already exists in the database
	 *
	 * @return  bool
	 */
	public function exists() {
		return !is_null($this->{$this->primaryKey});
	}

	/**
	 * Saves model data to the database.
	 *
	 * @return  array
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

	/**
	 * Returns true if the specific data property exists.
	 *
	 * Alternatively, you can just use `isset($model->data[$prop])`
	 *
	 * @param  string $prop Key of the property you want.
	 * @return boolean
	 */
	public function __isset($prop) {
		return isset($this->data[$prop]);
	}

	/**
	 * Returns the specific data property as if it were an actual top-level property.
	 *
	 * Alternatively, you can just use `$model->data[$prop]`
	 *
	 * @param  string $prop Key of the property you want.
	 * @return mixed
	 */
	public function __get($prop) {
		if (isset($this->data[$prop])) {
			return $this->data[$prop];
		}
		if (isset($this->relData[$prop])) {
			return $this->relData[$prop];
		}
	}

	/**
	 * Sets the specified data property.
	 *
	 * Alternatively, you can just use `$model->data[$prop] = $val`
	 *
	 * @param   string $prop [description]
	 * @param   mixed  $val  [description]
	 * @return  mixed
	 */
	public function __set($prop, $val) {
		if (isset($this->relData[$prop])) {
			return ($this->relData[$prop] = $val);
		}
		return ($this->data[$prop] = $val);
	}

	/**
	 * Create a new model object.
	 * Really just an alias for `new Model()`
	 *
	 * Note: Does not save to the database.
	 *
	 * @param   array $data
	 * @return  object
	 */
	public static function create($data=array()) {
		return new static($data);
	}

	/**
	 * Query all records from the database
	 * and return as an array.
	 *
	 * @param   array $conditions
	 * @param   array $options
	 * @return  array
	 */
	public static function all($conditions = array(), $options = array()) {
		return static::find('all', $conditions, $options);
	}

	/**
	 * Query a single record from the database
	 * and return model instance.
	 *
	 * @param   array $conditions
	 * @param   array $options
	 * @return  mixed
	 */
	public static function first($conditions = array(), $options = array()) {
		return static::find('first', $conditions, $options + array(
			'limit' => 1,
		));
	}

	/**
	 * Generic find.
	 *
	 * @param  string $type       Type of find: 'all' or 'first'.
	 * @param  array  $conditions
	 * @param  array  $options
	 * @return mixed
	 */
	public static function find($type, $conditions = array(), $options = array()) {
		$options += array(
			'with' => array(),
		);
		$with = static::mergeWith($options['with']);
		unset($options['with']);

		$query = new Query($options + array(
			'model' => get_called_class(),
			'conditions' => $conditions,
		));
		$db = static::connection();
		$results = $db->read($query);
		foreach ($results as &$result) {
			$result = new static($result);
		}
		$results = static::relationships($results, $with);

		if ($type === 'first' && count($results) > 0) {
			return $results[0];
		}
		return $results;
	}

	/**
	 * Will merge the current 'with' option with defaults set in the models.
	 *
	 * @param  array $with
	 * @return array
	 */
	public static function mergeWith(array $with) {
		foreach ($with as $name => $trueOptions) {
			if (is_int($name)) {
				unset($with[$name]);
				$name = $trueOptions;
				$with[$name] = $trueOptions = array();
			}
			$relation = static::relations($name);
			if (isset($relation['data']['options'])) {
				$with[$name] += $relation['data']['options'];
			}
		}
		return $with;
	}

	/**
	 * Setups up relationships.
	 *
	 * @param   array $results
	 * @param   array $with
	 * @return  array
	 */
	public static function relationships($results, $with) {
		if (count($results) === 0 || count($with) === 0) {
			return $results;
		}
		$first = $results[0];
		foreach ($with as $key => $value) {
			$relationshipInfo = static::_determineChildInfo($key, $value);
			$relationship = $first->retrieveRelationship($relationshipInfo['name']);
			$relationship->options($relationshipInfo['options']);
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
				'options' => $value,
			);
		}
		return array(
			'name' => $value,
			'options' => array(),
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

	/**
	 * Return meta information, for compatibility with LI3.
	 *
	 * @param string $key - name of property to return, e.g.
	 *                      'name' or 'source'
	 * @param string $val - ignored
	 */
	public static function meta($key = null, $val = null) {
		$class = get_called_class();
		$parts = explode("\\", $class);
		$name = $parts[count($parts)-1];
		if($key == 'name') {
			return strtolower($name);
		} else if($key == 'source') {
			return static::$sourceName ? static::$sourceName : strtolower(Inflector::tableize($name));
		}
	}

	/**
	 * Returns an empty schema array.
	 *
	 * We don't support schema, but LI3 Query still looks for it.
	 *
	 * @return  array
	 */
	public static function schema() {
		return array();
	}

	/**
	 * Fetch and return the LI3 database connection named in
	 * static $connectionName, wrapped in our own fake connection
	 * adapter :-)
	 *
	 * @return  object Connection
	 */
	public static function connection() {
		if (!isset(static::$cachedConnection)) {
			$conn = Connections::get(static::$connectionName);
			$connClass = get_class($conn);
			if (preg_match('/MongoDb/', $connClass)) {
				$db = static::$classes['database'];
				static::$cachedConnection = new $db($conn);
			} else {
				throw new ConfigException('not yet implemented');
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