<?php
namespace li3_fake_model\tests\mocks\extensions\data;

class MockGrandchildModel extends \li3_fake_model\extensions\data\Model {
	public $hasOne = array(
		'MockChildModel' => array(
			'to'        => 'li3_fake_model\tests\mocks\extensions\data\MockChildModel',
			'key'       => array('parent_id' => '_id'),
			'fieldName' => 'parent',
		),
	);
}