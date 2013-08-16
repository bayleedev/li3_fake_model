<?php

namespace li3_fake_model\tests\mocks\extensions\data;

use li3_fake_model\extensions\data\Model;

class MockMasterModel extends Model {

	public $hasOne = array(
		'FavoriteDog' => array(
			'to'        => 'li3_fake_model\tests\mocks\extensions\data\MockDogModel',
			'key'       => array('dog_ids' => '_id'),
			'options'   => array(
				'order' => array('age' => 'DESC')
			),
			'fieldName' => 'favoriteDog',
		),
	);

}