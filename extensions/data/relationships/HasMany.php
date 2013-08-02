<?php

namespace li3_fake_model\extensions\data\relationships;

class HasMany extends Relation {

	public function appendData() {
		$foreignField = current($this->meta['key']);
		$currentField = key($this->meta['key']);
		$fieldName = $this->meta['fieldName'];
		foreach ($this->data as $data) {
			foreach ($this->results() as $result) {
				$reverseRelation = $result->retrieveRelationship(get_class($data));
				$type = $reverseRelation instanceof $result->classes['hasOne'] ? 'hasOne' : 'hasMany';
				if ($this->compare($type, $result->{$foreignField}, $data->{$currentField})) {
					$data->data[$fieldName][] = $result;
				}
			}
		}
		return;
	}

	public function compare($type, $result1, $result2) {
		if ($type === 'hasOne') {
			return $result1 == $result2;
		}
		return in_array($result1, $result2);
	}

	public function results() {
		$class = $this->meta['to'];
		return $class::all(
			array(
				current($this->meta['key']) => array(
					'$in' => $this->retrieveFields(),
				),
			),
			array(
				'with' => $this->with(),
			)
		);
	}

}