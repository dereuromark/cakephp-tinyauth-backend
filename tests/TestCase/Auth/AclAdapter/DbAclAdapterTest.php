<?php

namespace TinyAuthBackend\Test\TestCase\Auth\AclAdapter;

use Cake\TestSuite\TestCase;
use TinyAuthBackend\Auth\AclAdapter\DbAclAdapter;

class DbAclAdapterTest extends TestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	protected $fixtures = [
		'plugin.TinyAuthBackend.TinyAuthAclRules',
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
