<?php

namespace li3_fake_model\extensions\data\relationships;

use lithium\core\ConfigException;

class HasMany extends Relation {

	public function appendData() {
		$foreignField = current($this->meta['key']);
		$currentField = key($this->meta['key']);
		$fieldName = $this->meta['fieldName'];
		foreach ($this->results() as $result) {
			foreach ($this->data as $data) {
				try {
					$reverseRelation = $result->retrieveRelationship(get_class($data));
					$type = $reverseRelation instanceof $result::$classes['hasOne'] ? 'hasOne' : 'hasMany';
				} catch (ConfigException $e) {
					$type = 'hasMany';
				}
				if ($this->compare($type, $result->{$foreignField}, $data->{$currentField})) {
					$data->relData[$fieldName][] = $result;
				}
			}
		}
		return;
	}

	public function compare($type, $result1, $result2) {
		if ($type === 'hasOne' || (!is_array($result1) && !is_array($result2))) {
			return $result1 == $result2;
		}
		if (!is_array($result2)) {
			return in_array($result2, $result1);
		}
		return in_array($result1, $result2);
	}

	public function results() {
		if (!empty($this->results)) {
			return $this->results;
		}
		$class = $this->meta['to'];
		return ($this->results = $class::all(
			$this->conditions(),
			$this->queryOptions()
		));
	}

	public function conditions() {
		return array(
				current($this->meta['key']) => array(
					'$in' => $this->retrieveFields(),
				),
			) + parent::conditions();
	}

}