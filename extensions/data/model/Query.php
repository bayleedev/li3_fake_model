<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2012, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_fake_model\extensions\data\model;

class Query extends \lithium\data\model\Query {

	/**
	 * Generic getter/setter for config.
	 *
	 * @param  string $key   Optional
	 * @param  mixed  $value Optional
	 * @return string
	 */
	public function config($key = null, $value = null) {
		if ($key === null || !isset($this->_config[$key])) {
			return $this->_config;
		}
		if ($value !== null) {
			$this->_config[$key] = $value;
		}
		return $this->_config[$key];
	}
}

?>