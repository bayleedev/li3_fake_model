<?php

namespace li3_fake_model\extensions\data\relationships;

class HasOne extends Relation {

	public function appendData() {
		$foreignField = current($this->meta['key']);
		$currentField = key($this->meta['key']);
		$fieldName = $this->meta['fieldName'];
		foreach ($this->data as $data) {
			foreach ($this->results() as $result) {
				if (is_array($data->{$currentField}) && in_array($result->{$foreignField}, $data->{$currentField})) {
					$data->relData[$fieldName] = $result;
					break;
				} else if ($result->{$foreignField} == $data->{$currentField}) {
					$data->relData[$fieldName] = $result;
				}
			}
		}
		return;
	}

	public function results() {
		if (!empty($this->results)) {
			return $this->results;
		}
		$class = $this->meta['to'];
		return ($this->results = $class::all(
			array(
				current($this->meta['key']) => array(
					'$in' => $this->retrieveFields(),
				),
			),
			$this->options()
		));
	}

}