<?php

namespace li3_fake_model\extensions\data\relationships;

class HasManyEmbedded extends Relation {

	public function appendData() {
		$key = $this->meta['fieldName'];
		$class = $this->meta['to'];
		foreach ($this->data as &$row) {
			if (!empty($row->data[$key]) && is_array($row->data[$key])) {
				foreach ($row->data[$key] as $k => &$v) {
					$row->data[$key][$k] = new $class($v);
				}
			}
		}
		return;
	}

}