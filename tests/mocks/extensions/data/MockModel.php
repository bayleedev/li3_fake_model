<?php

namespace li3_fake_model\tests\mocks\extensions\data;

class MockModel extends \li3_fake_model\extensions\data\Model {

	public $hasMany = array(
		'MockChildModel' => array(
			'to'        => 'li3_fake_model\tests\mocks\extensions\data\MockChildModel',
			'key'       => array('child_ids' => '_id'),
			'fieldName' => 'children',
		),
	);

}