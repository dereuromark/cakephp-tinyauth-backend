<?php
namespace App\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use TinyAuthBackend\Model\Entity\AclRule;
use TinyAuthBackend\Model\Table\AclRulesTable;

class AclRulesTableTest extends TestCase {

	/**
	 * Test subject
	 *
	 * @var \TinyAuthBackend\Model\Table\AclRulesTable
	 */
	public $AclRules;

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'plugin.TinyAuthBackend.TinyAuthAclRules'
	];

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$config = TableRegistry::exists('AclRules') ? [] : ['className' => AclRulesTable::class];
		$this->AclRules = TableRegistry::get('AclRules', $config);
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
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
			'path' => 'Foo',
			'role' => 'user',
		];
		$aclRule = $this->AclRules->newEntity($data);

		$result = $this->AclRules->save($aclRule);
		$this->assertTrue(!empty($result));
		$this->assertInstanceOf(AclRule::class, $result);
	}

}
