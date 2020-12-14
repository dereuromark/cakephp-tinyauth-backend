<?php

namespace TinyAuthBackend\Test\TestCase\Auth\AllowAdapter;

use Cake\TestSuite\TestCase;
use TinyAuthBackend\Auth\AllowAdapter\DbAllowAdapter;

class DbAllowAdapterTest extends TestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	protected $fixtures = [
		'plugin.TinyAuthBackend.TinyAuthAllowRules',
	];

	/**
	 * @return void
	 */
	public function testGetAllow() {
		$adapter = new DbAllowAdapter();

		$config = [];
		$result = $adapter->getAllow($config);

		$this->assertSame([], $result);
	}

}
