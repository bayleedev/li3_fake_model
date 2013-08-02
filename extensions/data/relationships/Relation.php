<?php

namespace li3_fake_model\extensions\data\relationships;

abstract class Relation {

	public $data = array();

	public $meta = array();

	public $with = array();

	public function __construct(array $meta = array()) {
		$this->meta = $meta;
		$this->with = array();
	}

	public function data(array $data = null) {
		if ($data === null) {
			return $this->data;
		}
		return $this->data = $data;
	}

	public function with(array $with = null) {
		if ($with === null) {
			return $this->with;
		}
		return $this->with = $with;
	}

	public function retrieveFields() {
		$fields = array();
		$currentField = key($this->meta['key']);
		foreach ($this->data as $data) {
			if (is_array($data->{$currentField})) {
				$fields = array_merge($fields, $data->{$currentField});
			} else {
				$fields[] = $data->{$currentField};
			}
		}
		return $fields;
	}

	public abstract function appendData();

}