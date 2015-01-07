<?php

namespace li3_fake_model\extensions\data\relationships;

abstract class EmbeddedRelation extends Relation {

	/**
	 * Embedded relationships never get relationships.
	 *
	 * This happens inside of `Model::find`, which isn't
	 * triggered on embedded relationships.
	 *
	 * @param array $data
	 * @return array
	 */
	protected function embeddedRelationships($data) {
		if (count($data) > 0) {
			$with = array_merge($this->meta['with'], $this->options['with']);
			return $data[0]::relationships($data, $with);
		}
		return $data;
	}

}
