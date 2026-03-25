<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase\Service;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use TinyAuthBackend\Service\HierarchyService;

class HierarchyServiceTest extends TestCase {

	protected array $fixtures = [
		'plugin.TinyAuthBackend.TinyAuthRoles',
	];

	public function setUp(): void {
		parent::setUp();

		$this->insertRow('tinyauth_roles', [
			'id' => 1,
			'name' => 'Administrator',
			'alias' => 'admin',
			'parent_id' => null,
			'sort_order' => 30,
		]);
		$this->insertRow('tinyauth_roles', [
			'id' => 2,
			'name' => 'Moderator',
			'alias' => 'moderator',
			'parent_id' => 1,
			'sort_order' => 20,
		]);
		$this->insertRow('tinyauth_roles', [
			'id' => 3,
			'name' => 'User',
			'alias' => 'user',
			'parent_id' => 2,
			'sort_order' => 10,
		]);
	}

	public function testApplyInheritancePromotesPermissionsToParents(): void {
		$service = new HierarchyService();
		$acl = [
			'Articles' => [
				'plugin' => null,
				'prefix' => null,
				'controller' => 'Articles',
				'allow' => [
					'edit' => ['user' => 3],
				],
				'deny' => [],
			],
		];

		$result = $service->applyInheritance($acl, [
			'admin' => 1,
			'moderator' => 2,
			'user' => 3,
		]);

		$this->assertSame(
			['user' => 3, 'moderator' => 2, 'admin' => 1],
			$result['Articles']['allow']['edit'],
		);
	}

	protected function insertRow(string $table, array $data): void {
		TableRegistry::getTableLocator()->get($table)->getConnection()->insert($table, $data);
	}

}
