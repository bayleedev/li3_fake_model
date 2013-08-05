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

}

?>