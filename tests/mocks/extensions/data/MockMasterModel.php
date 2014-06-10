<?php

namespace li3_fake_model\tests\mocks\extensions\data;

use li3_fake_model\extensions\data\Model;

class MockMasterModel extends Model {

	public $hasMany = array(
		'FavoriteDogs' => array(
			'to'        => 'li3_fake_model\tests\mocks\extensions\data\MockDogModel',
			'key'       => array('dog_id' => '_id'),
			'fieldName' => 'favoriteDogs',
			'order' => array('age' => 'DESC'),
			'with' => array(
				'MockGrandchildModel' => array(
					'conditions' => array(
						'name' => 'jim',
					),
				),
			),
		),
	);

	public $hasOne = array(
		'FavoriteDog' => array(
			'to'        => 'li3_fake_model\tests\mocks\extensions\data\MockDogModel',
			'key'       => array('dog_id' => '_id'),
			'fieldName' => 'favoriteDog',
			'order' => array('age' => 'DESC'),
		),
	);

}