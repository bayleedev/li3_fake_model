<?php

namespace li3_fake_model\tests\cases\extensions\data;

use li3_fake_model\tests\mocks\extensions\data\MockModel;
use li3_fake_model\tests\mocks\extensions\data\MockChildModel;
use li3_fake_model\tests\mocks\extensions\data\MockGrandchildModel;

use \app\models\ProviderPractices;

use lithium\data\Connections;

class ModelTest extends \app\extensions\test\Unit {

	public function setUp() {
		$this->child = MockChildModel::create(array(
			'level'     => 2,
		));
		$this->child->save();
		$this->grandchild = MockGrandchildModel::create(array(
			'level'     => 3,
			'parent_id' => $this->child->_id,
		));
		$this->grandchild->save();
		$this->parent = MockModel::create(array(
			'level'     => 1,
			'child_ids' => array($this->child->_id),
		));
		$this->parent->save();

		$this->db = Connections::get('default')->connection;
	}

	public function testMetaName() {
		$this->assertIdentical('MockModel', MockModel::meta('name'));
	}

	public function testMetaSource() {
		$this->assertIdentical('mock_models', MockModel::meta('source'));
	}

	// ensure that record was inserted in setUp() method
	public function testCreate() {
		$collection = $this->db->mock_models;
		$record = $collection->findOne();
		$this->assertIdentical(1, $record['level']);
	}

	public function testStoreId() {
		$this->assertNotNull($this->parent->_id);
	}

	public function testIdNullForNewRecord() {
		$newRecord = MockModel::create();
		$this->assertNull($newRecord->_id);
	}

	public function testUpdate() {
		$this->parent->data['level'] = 10;
		$this->parent->save();
		$collection = $this->db->mock_models;
		$record = $collection->findOne();
		$this->assertIdentical(10, $record['level']);
	}

	public function testFirst() {
		$record = MockModel::first();
		$this->assertEqual($this->parent, $record);
	}

	public function testAll() {
		$records = MockModel::all();
		$this->assertEqual(array($this->parent), $records);
	}

	public function testDataGetter() {
		$this->assertIdentical(1, $this->parent->level);
	}

	public function testDataSetter() {
		$this->parent->level = 100;
		$this->assertIdentical(100, $this->parent->data['level']);
	}

	public function testNoRelation() {
		$parent = MockModel::first();
		$this->assertNull($parent->children);
	}

	//public function testFirstLevelRelation() {
		//$parent = MockModel::first(array(), array(
			//'with' => array('MockChildModel'),
		//));
		//$this->assertNotEmpty($parent->children);
	//}

}