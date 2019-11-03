<?php

namespace TinyAuthBackend\Auth\AclAdapter;

use Cake\TestSuite\TestCase;

class DbAclAdapterTest extends TestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'plugin.TinyAuthBackend.TinyAuthAclRules'
	];

	/**
	 * @return void
	 */
	public function testGetAcl() {
		$adapter = new DbAclAdapter();

		$roles = [];
		$config = [];
		$result = $adapter->getAcl($roles, $config);

		$this->assertSame([], $result);
	}

}
