<?php

namespace li3_fake_model\extensions\data\relationships;

class HasManyEmbedded extends EmbeddedRelation {

	public function appendData() {
		$key = $this->meta['fieldName'];
		$class = $this->meta['to'];
		$embedded = array();
		foreach ($this->data as &$row) {
			if (!empty($row->data[$key]) && is_array($row->data[$key])) {
				foreach ($row->data[$key] as $k => &$v) {
					$row->data[$key][$k] = $embedded[] = new $class($v, $this->queryOptions());
				}
			}
		}
		return static::embeddedRelationships($embedded);
	}

}