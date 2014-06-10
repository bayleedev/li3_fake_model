<?php

namespace li3_fake_model\extensions\data\relationships;

abstract class Relation {

	public $data = array();

	public $meta = array();

	public $options = array();

	public $results = array();

	public function __construct(array $meta = array()) {
		$this->meta($meta);
		$this->options(array());
	}

	public function data(array $data = null) {
		if ($data === null) {
			return $this->data;
		}
		return $this->data = $data;
	}

	public function meta(array $meta = null) {
		if ($meta === null) {
			return $this->options;
		}
		return $this->meta = $meta += array(
			'with' => array(),
			'limit' => false,
			'order' => array(),
			'conditions' => array(),
		);
	}

	public function options(array $options = null) {
		if ($options === null) {
			return $this->options;
		}
		return $this->options = $options += array(
			'with' => array(),
			'limit' => false,
			'order' => array(),
			'conditions' => array(),
		);
	}

	public function __call($method, $params) {
		if (in_array($method, array('with', 'limit', 'order', 'conditions'))) {
			return $this->options[$method] + $this->meta[$method];
		}
		return;
	}

	public function queryOptions() {
		return array_filter(array(
			'with' => $this->with(),
			'order' => $this->order(),
			'limit' => $this->limit(),
		), function($i) {
			return !empty($i);
		});
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