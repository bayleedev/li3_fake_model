<?php

namespace li3_fake_model\tests\mocks\extensions\data;

use li3_fake_model\extensions\data\Model;

class MockDogModel extends Model {

	public $hasOne = array(
		'MockGrandchildModel' => array(
			'to'        => 'li3_fake_model\tests\mocks\extensions\data\MockGrandchildModel',
			'key'       => array('_id' => 'dog_id'),
			'fieldName' => 'owner',
		),
	);

	public $hasMany = array(
		'Bones' => array(
			'to'        => 'li3_fake_model\tests\mocks\extensions\data\MockBoneModel',
			'key'       => array('bone_ids' => '_id'),
			'fieldName' => 'bones',
		),
	);

}