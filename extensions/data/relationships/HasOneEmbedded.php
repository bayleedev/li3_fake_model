<?php

namespace li3_fake_model\extensions\data\relationships;

class HasOneEmbedded extends EmbeddedRelation {

	public function appendData() {
		$key = $this->meta['fieldName'];
		$class = $this->meta['to'];
		$embedded = array();
		foreach ($this->data as &$row) {
			if (!empty($row->data[$key])) {
				$row->data[$key] = $embedded[] = new $class($row->data[$key]);
			}
		}
		return static::embeddedRelationships($embedded);
	}

}