<?php

namespace li3_fake_model\extensions\data\relationships;

abstract class Relation {

	public $data = array();

	public $meta = array();

	public $options = array();

	public $results = array();

	public function __construct(array $meta = array()) {
		$this->meta = $meta;
		$this->options = array();
	}

	public function data(array $data = null) {
		if ($data === null) {
			return $this->data;
		}
		return $this->data = $data;
	}

	public function options(array $options = null) {
		if ($options === null) {
			return $this->options;
		}
		return $this->options = $options;
	}

	public function retrieveFields() {
		if (!empty($this->fields)) {
			return $this->fields;
		}
		$fields = array();
		$currentField = key($this->meta['key']);
		foreach ($this->data as $key => $data) {
			if (is_array($data->data[$currentField])) {
				$fields = array_merge($fields, $data->data[$currentField]);
			} else {
				$fields[] = $data->data[$currentField];
			}
		}
		return ($this->fields = array_unique($fields));
	}

	public abstract function appendData();

}