<?php

namespace li3_fake_model\extensions\data;

use Countable;
use ArrayAccess;
use Iterator;

class Collection implements Countable, ArrayAccess, Iterator {

	public $data;

	public $position = 0;

	public function __construct($data) {
		$this->data = $data;
	}

	/**
	 * Determines the count of this Collection.
	 *
	 * For Countable Interface
	 *
	 * @return int
	 */
	public function count() {
		return count($this->data);
	}

	/**
	 * Sets ont he current offset.
	 *
	 * For the ArrayAccess Interface.
	 *
	 * @param  mixed $offset
	 * @param  mixed $value
	 * @return null
	 */
	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			$this->data[] = $value;
		} else {
			$this->data[$offset] = $value;
		}
	}

	/**
	 * Determines if this offset exists in this Collection.
	 *
	 * For the ArrayAccess Interface.
	 *
	 * @param  mixed $offset
	 * @return boolean
	 */
	public function offsetExists($offset) {
		return isset($this->data[$offset]);
	}

	/**
	 * Unset a specific offest in this Collection.
	 *
	 * For the ArrayAccess Interface.
	 *
	 * @param  mixed $offset
	 * @return null
	 */
	public function offsetUnset($offset) {
		unset($this->data[$offset]);
	}

	/**
	 * Gets a specific offset in this Collection.
	 *
	 * For the ArrayAccess Interface.
	 *
	 * @param  mixed $offset
	 * @return mixed
	 */
	public function offsetGet($offset) {
		return isset($this->data[$offset]) ? $this->data[$offset] : null;
	}

	/**
	 * Rewinds the current pointer.
	 *
	 * For the Iterator Interface.
	 *
	 * @return null
	 */
	public function rewind() {
		$this->position = 0;
	}

	/**
	 * Retrieves the current value we are pointing at.
	 *
	 * For the Iterator Interface.
	 *
	 * @return mixed
	 */
	public function current() {
		return $this->data[$this->position];
	}

	/**
	 * Retrieves the current key we are pointing at.
	 *
	 * For the Iterator Interface.
	 *
	 * @return mixed
	 */
	public function key() {
		return $this->position;
	}

	/**
	 * Increase the current pointer.
	 *
	 * For the Iterator Interface.
	 *
	 * @return null
	 */
	public function next() {
		++$this->position;
	}

	/**
	 * Determines if the current pointer is valid.
	 *
	 * For the Iterator Interface.
	 *
	 * @return boolean
	 */
	public function valid() {
		return isset($this->data[$this->position]);
	}

}