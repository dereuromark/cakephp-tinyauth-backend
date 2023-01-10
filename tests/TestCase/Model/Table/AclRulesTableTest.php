<?php

namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use TinyAuthBackend\Model\Entity\AclRule;
use TinyAuthBackend\Model\Table\AclRulesTable;

class AclRulesTableTest extends TestCase {

	/**
  * Test subject
  */
	public AclRulesTable $AclRules;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	protected array $fixtures = [
		'plugin.TinyAuthBackend.TinyAuthAclRules',
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		$config = TableRegistry::getTableLocator()->exists('AclRules') ? [] : ['className' => AclRulesTable::class];
		$this->AclRules = TableRegistry::getTableLocator()->get('AclRules', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown(): void {
		unset($this->AclRules);

		parent::tearDown();
	}
	/**
	 * @return void
	 */
	public function testInstance() {
		$this->assertInstanceOf(AclRulesTable::class, $this->AclRules);
	}

	/**
	 * @return void
	 */
	public function testSave() {
		$data = [
			'type' => AclRule::TYPE_ALLOW,
			'path' => 'MyVendor/MyPlugin.MyPrefix/MySubPrefix/MyController::myAction',
			'role' => 'user',
		];
		$aclRule = $this->AclRules->newEntity($data);

		$result = $this->AclRules->save($aclRule);
		$this->assertTrue(!empty($result), print_r($aclRule->getErrors(), true));
		$this->assertInstanceOf(AclRule::class, $result);
	}

	/**
	 * @return void
	 */
	public function testSaveAutoInflected() {
		$data = [
			'type' => AclRule::TYPE_ALLOW,
			'path' => 'MyVendor/MyPlugin.my_prefix/my_sub_prefix/MyController::myAction',
			'role' => 'user',
		];
		$aclRule = $this->AclRules->newEntity($data);

		$result = $this->AclRules->save($aclRule);
		$this->assertTrue(!empty($result), print_r($aclRule->getErrors(), true));
		$this->assertInstanceOf(AclRule::class, $result);

		$expected = 'MyVendor/MyPlugin.MyPrefix/MySubPrefix/MyController::myAction';
		$this->assertSame($expected, $result->path);
	}

}
