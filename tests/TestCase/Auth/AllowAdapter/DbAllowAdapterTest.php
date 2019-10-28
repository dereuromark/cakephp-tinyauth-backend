<?php

namespace TinyAuthBackend\Auth\AllowAdapter;

use Cake\TestSuite\TestCase;

class DbAllowAdapterTest extends TestCase {

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
