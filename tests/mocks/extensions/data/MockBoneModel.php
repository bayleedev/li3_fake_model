<?php

namespace li3_fake_model\tests\mocks\extensions\data;

use li3_fake_model\extensions\data\Model;

class MockBoneModel extends Model {

	public $hasMany = array(
		'Dogs' => array(
			'to'        => 'li3_fake_model\tests\mocks\extensions\data\MockDogModel',
			'key'       => array('_id' => 'bone_ids'),
			'fieldName' => 'dogs',
		),
	);

}