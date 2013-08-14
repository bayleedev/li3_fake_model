<?php

namespace li3_fake_model\tests\cases\extensions\data;

use li3_fake_model\tests\mocks\extensions\data\MockModel;
use li3_fake_model\tests\mocks\extensions\data\MockChildModel;
use li3_fake_model\tests\mocks\extensions\data\MockGrandchildModel;
use li3_fake_model\tests\mocks\extensions\data\MockRealModel;
use li3_fake_model\tests\mocks\extensions\data\MockDogModel;
use li3_fake_model\extensions\test\Unit;

use lithium\data\Connections;

class ModelTest extends Unit {

	public function setUp() {
		$this->child = MockChildModel::create(array(
			'level'     => 2,
		));
		$this->child->save();
		$this->dog = MockDogModel::create(array(
			'name'     => 'Fido',
		));
		$this->dog->save();
		$this->grandchild = MockGrandchildModel::create(array(
			'level'     => 3,
			'parent_id' => $this->child->_id,
			'dog_id' => $this->dog->_id,
		));
		$this->grandchild->save();
		$this->parent = MockModel::create(array(
			'level'     => 1,
			'child_ids' => array(
				$this->child->_id,
			),
		));
		$this->parent->save();

		$this->db = Connections::get('default')->connection;
	}

	public function tearDown() {
		$connection = Connections::get('default');
		$mongo = $connection->connection;
		foreach($mongo->listCollections() as $collection) {
			$collection->drop();
		}
	}

	public function testMetaName() {
		$this->assertIdentical('mockmodel', MockModel::meta('name'));
	}

	public function testMetaSource() {
		$this->assertIdentical('mock_models', MockModel::meta('source'));
	}

	public function testSoureName() {
		MockModel::$sourceName = 'widgets';
		$record = new MockModel(array('foo' => 'bar'));
		$record->save();
		$collection = $this->db->widgets;
		$record = $collection->findOne();
		$this->assertIdentical('bar', $record['foo']);
		MockModel::$sourceName = null; // put this back for other tests to work!
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

	public function testFirstWithCondition() {
		$record = new MockModel(array('bar' => 'buz'));
		$record->save();
		$found = MockModel::first(array('bar' => 'buz'));
		$this->assertEqual($record, $found);
	}

	public function testFirstWithOffset() {
		$record = new MockModel(array('bar' => 'buz'));
		$record->save();
		$found = MockModel::first(array(), array('offset' => 1));
		$this->assertEqual($record, $found);
	}

	public function testFirstWithOrder() {
		$record = new MockModel(array('bar' => 'buz'));
		$record->save();
		$found = MockModel::first(array(), array('order' => array('bar' => 'desc')));
		$this->assertEqual($record, $found);
	}

	public function testAll() {
		$record = new MockModel(array('bar' => 'buz'));
		$record->save();
		$records = MockModel::all();
		$this->assertEqual(array($this->parent, $record), $records);
	}

	public function testAllWithCondition() {
		$record = new MockModel(array('bar' => 'buz'));
		$record->save();
		$records = MockModel::all(array('bar' => 'buz'));
		$this->assertEqual(array($record), $records);
	}

	public function testAllWithLimitAndOffset() {
		$record = new MockModel(array('bar' => 'buz'));
		$record->save();
		$records = MockModel::all(array(), array('limit' => 1, 'offset' => 1));
		$this->assertEqual(array($record), $records);
	}

	public function testAllWithOrder() {
		$record = new MockModel(array('bar' => 'buz'));
		$record->save();
		$records = MockModel::all(array(), array('order' => array('bar' => 'desc')));
		$this->assertEqual(array($record, $this->parent), $records);
	}

	public function testDataGetter() {
		$this->assertIdentical(1, $this->parent->level);
	}

	public function testDataIsset() {
		$this->assertIdentical(true, isset($this->parent->level));
		$this->assertIdentical(false, isset($this->parent->unknown));
	}

	public function testDataSetter() {
		$this->parent->level = 100;
		$this->assertIdentical(100, $this->parent->data['level']);
	}

	public function testNoRelation() {
		$parent = MockModel::first();
		$this->assertNull($parent->children);
	}

	public function testFirstSpeed() {
		if(!isset($_GET['benchmark'])) return;
		$record = MockRealModel::create(array(
			'foo' => 'bar'
		));
		$record->save();
		$this->benchmark('FakeModel::first()', function() {
			MockModel::first();
		});
		$this->benchmark('RealModel::first()', function() {
			MockRealModel::first();
		});
	}

	public function testAllSpeed() {
		if(!isset($_GET['benchmark'])) return;
		for($i=0; $i<100; $i++) {
			$record = MockModel::create(array(
				'foo' => 'bar'
			));
			$record->save();
			$record = MockRealModel::create(array(
				'foo' => 'bar'
			));
			$record->save();
		}
		$this->benchmark('FakeModel::all()', function() {
			$all = MockModel::all();
			foreach($all as $rec) { }
		});
		$this->benchmark('RealModel::all()', function() {
			$all = MockRealModel::all();
			foreach($all as $rec) { } // force loading all records
		});
	}

	public function benchmark($name, $func, $count=100) {
		$start = microtime(TRUE);
		for($i=0; $i<$count; $i++) {
			$func();
		}
		echo "<pre>$name : " . round((microtime(TRUE) - $start) * 1000, 2) . ' ms</pre>';
	}

	public function testFirstLevelRelationCount() {
		$parent = MockModel::first(array(), array(
			'with' => array('MockChildModel'),
		));
		$this->assertCount(1, $parent->children);
	}

	public function testFirstLevelRelationItem() {
		$parent = MockModel::first(array(), array(
			'with' => array('MockChildModel'),
		));
		$this->assertEqual($this->child, $parent->children[0]);
	}

	public function testParentItem() {
		$item = MockGrandchildModel::first(array(), array(
			'with' => array('MockChildModel'),
		));
		$this->assertEqual($this->child, $item->parent);
	}

	public function testHasOneToHasOneRelationshiopPersonWithDog() {
		$person = MockGrandchildModel::first(array(), array(
			'with' => array('MockDogModel'),
		));
		$this->assertEqual($this->dog, $person->dog);
	}

	public function testHasOneToHasOneRelationshiopDogWithPerson() {
		$dog = MockDogModel::first(array(), array(
			'with' => array('MockGrandchildModel'),
		));
		$this->assertEqual($this->grandchild, $dog->owner);
	}

	public function testTwoLevelRelationshipHasCorrectResults() {
		$child = MockChildModel::first(array(), array(
			'with' => array(
				'MockGrandchildModel' => array(
					'with' => array('MockDogModel'),
				),
			),
		));
		$this->assertEqual($this->dog, $child->children[0]->dog);
	}

	public function testTwoLevelRelationshipHasCorrectQueryCount() {
		$class = 'li3_fake_model\tests\mocks\extensions\data\MockChildModel';
		$this->assertQueryCount($class, 3, function() {
			MockChildModel::first(array(), array(
				'with' => array(
					'MockGrandchildModel' => array(
						'with' => array('MockDogModel'),
					),
				),
			));
		});
	}

	public function testThreeLevelRelationshipHasCorrectResults() {
		$model = MockModel::first(array(), array(
			'with' => array(
				'MockChildModel' => array(
					'with' => array(
						'MockGrandchildModel' => array(
							'with' => array('MockDogModel'),
						),
					),
				),
			),
		));
		$this->assertEqual($this->dog, $model->children[0]->children[0]->dog);
	}

	public function testThreeLevelRelationshipHasCorrectQueryCount() {
		$class = 'li3_fake_model\tests\mocks\extensions\data\MockModel';
		$this->assertQueryCount($class, 4, function() {
			MockModel::first(array(), array(
				'with' => array(
					'MockChildModel' => array(
						'with' => array(
							'MockGrandchildModel' => array(
								'with' => array('MockDogModel'),
							),
						),
					)
				),
			));
		});
	}

	public function testEmptyResult() {
		$result = MockModel::first(array(
			'conditions' => array(
				'foo' => 'bar',
			),
		));
		$this->assertEmpty($result);
	}

	public function testRecreatingRelationships() {
		foreach (range(2, 10) as $level) {
			MockModel::create(array(
				'level'     => $level,
				'child_ids' => array(
					$this->child->_id,
				),
			))->save();
		}
		$class = 'li3_fake_model\tests\mocks\extensions\data\MockModel';
		$this->assertQueryCount($class, 2, function() {
			MockModel::all(array(), array(
				'with' => array(
					'MockChildModel',
				),
			));
		});
	}

	public function testTwoLevelRelationshipOverwritesLimit() {
		$class = 'li3_fake_model\tests\mocks\extensions\data\MockModel';
		$queries = array(
			array(
				'name' => 'mock_models',
				'limit' => 1,
			),
			array(
				'conditions' => array(
					'_id' => array(
						'$in' => array(
							$this->child->_id,
						),
					),
				),
				'name' => 'mock_child_models',
				'limit' => 12,
			),
		);
		$this->assertQueries($class, $queries, function() {
			MockModel::first(array(), array(
				'with' => array(
					'MockChildModel' => array(
						'limit' => 12,
					)
				),
			));
		});
	}

	public function testTwoLevelRelationshipUsesDefaultLimit() {
		$class = 'li3_fake_model\tests\mocks\extensions\data\MockModel';
		$queries = array(
			array(
				'name' => 'mock_models',
				'limit' => 1,
			),
			array(
				'conditions' => array(
					'_id' => array(
						'$in' => array(
							$this->child->_id,
						),
					),
				),
				'name' => 'mock_child_models',
				'limit' => 10,
			),
		);
		$this->assertQueries($class, $queries, function() {
			MockModel::first(array(), array(
				'with' => array(
					'MockChildModel',
				),
			));
		});
	}

}