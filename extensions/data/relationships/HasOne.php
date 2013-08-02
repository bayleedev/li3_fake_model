<?php

namespace li3_fake_model\extensions\data\relationships;

class HasOne extends Relation {

	public function appendData() {
		$foreignField = current($this->meta['key']);
		$currentField = key($this->meta['key']);
		$fieldName = $this->meta['fieldName'];
		foreach ($this->data as $data) {
			foreach ($this->results() as $result) {
				if ($result->{$foreignField} == $data->{$currentField}) {
					$data->data[$fieldName] = $result;
				}
			}
		}
		return;
	}

	public function results() {
		$class = $this->meta['to'];
		return $class::all(array(
			current($this->meta['key']) => array(
				'$in' => $this->retrieveFields(),
			),
		));
	}

}