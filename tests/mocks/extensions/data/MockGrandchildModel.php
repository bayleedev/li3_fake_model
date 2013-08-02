<?php

namespace li3_fake_model\tests\mocks\extensions\data;

use li3_fake_model\extensions\data\Model;

class MockGrandchildModel extends Model {

	public $hasOne = array(
		'MockChildModel' => array(
			'to'        => 'li3_fake_model\tests\mocks\extensions\data\MockChildModel',
			'key'       => array('parent_id' => '_id'),
			'fieldName' => 'parent',
		),
		'MockDogModel' => array(
			'to'        => 'li3_fake_model\tests\mocks\extensions\data\MockDogModel',
			'key'       => array('dog_id' => '_id'),
			'fieldName' => 'dog',
		),
	);

}