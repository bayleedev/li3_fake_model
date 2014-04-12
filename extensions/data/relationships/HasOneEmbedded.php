<?php

namespace li3_fake_model\extensions\data\relationships;

class HasOneEmbedded extends Relation {

	public function appendData() {
		$key = $this->meta['fieldName'];
		$class = $this->meta['to'];
		foreach ($this->data as &$row) {
			if (!empty($row->data[$key])) {
				$row->data[$key] = new $class($row->data[$key]);
			}
		}
		return;
	}

}