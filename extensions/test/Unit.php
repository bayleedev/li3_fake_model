<?php

namespace li3_fake_model\extensions\test;

class Unit extends \lithium\test\Unit {

	/**
	 * Asserts the number of `read` methods that were called on a given model.
	 *
	 * This assumes the `read` method is filterable on the data-source.
	 *
	 * @param  string    $class     Fully namespaced model class.
	 * @param  int       $expected  Number of queries expected.
	 * @param  callback  $query     Callback to be executed, contains the `::find` or `::all`.
	 * @param  string    $message   optional
	 * @return bool
	 */
	public function assertQueryCount($class, $expected, $query, $message = '{:message}') {
		$result = 0;
		$connection = $class::connection();
		$connection->applyFilter('read', function($self, $params, $chain) use(&$result) {
			$result++;
			return $chain->next($self, $params, $chain);
		});
		$query->__invoke();
		$connection->applyFilter('read', false);
		return $this->assert($result === $expected, $message, compact('expected', 'result'));
	}

	/**
	 * Asserts specific queries going into the database. Currently assumes mongo.
	 *
	 * Yikes this is a stubby method, but honey badger don't give a shit.
	 *
	 * {{{
	 * $this->assertQueries()
	 * }}}
	 *
	 * @param  string    $class     Fully namespaced model class.
	 * @param  int       $expected  Number of queries expected.
	 * @param  callback  $query     Callback to be executed, contains the `::find` or `::all`.
	 * @param  string    $message   optional
	 * @return bool
	 */
	public function assertQueries($class, $expected, $query, $message = '{:message}') {
		$result = array();
		$args = array();
		$connection = $class::connection();
		$connection->applyFilter('read', function($self, $params, $chain) use(&$result, &$args) {
			$args = $params['query']->export($self);
			$queryParams = array(
				'name' => $params['query']->config('source'),
			);
			foreach (array('conditions', 'fields', 'order', 'limit', 'offset') as $key) {
				if (!empty($args[$key])) {
					$queryParams[$key] = $args[$key];
				}
			}
			$result[] = $queryParams;
			return $chain->next($self, $params, $chain);
		});
		$query->__invoke();
		$connection->applyFilter('read', false);
		return $this->assertEqual($result == $expected, $message, compact('expected', 'result'));
	}

}

?>