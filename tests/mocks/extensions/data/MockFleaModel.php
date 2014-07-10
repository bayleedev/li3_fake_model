<?php

namespace li3_fake_model\tests\mocks\extensions\data;

use li3_fake_model\extensions\data\Model;

class MockFleaModel extends Model {

	public $hasMany = array(
		'Germs' => array(
			'to'        => 'li3_fake_model\tests\mocks\extensions\data\MockGermModel',
			'key'       => array('germ_ids' => '_id'),
			'fieldName' => 'germs',
		),
	);

}