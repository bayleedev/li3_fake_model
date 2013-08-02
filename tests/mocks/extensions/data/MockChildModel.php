<?php
namespace li3_fake_model\tests\mocks\extensions\data;

class MockChildModel extends \li3_fake_model\extensions\data\Model {

	public $hasMany = array(
		'MockGrandchildModel' => array(
			'to'        => 'li3_fake_model\tests\mocks\extensions\data\MockGrandchildModel',
			'key'       => array('_id' => 'parent_id'),
			'fieldName' => 'children',
		),
	);

}