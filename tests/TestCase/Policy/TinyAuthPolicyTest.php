<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase\Policy;

use Cake\Core\Configure;
use Cake\ORM\Entity;
use Cake\TestSuite\TestCase;
use TestApp\Model\Entity\Article;
use TinyAuthBackend\Policy\TinyAuthPolicy;
use TinyAuthBackend\Test\TestSuite\DatabaseTestTrait;

class TinyAuthPolicyTest extends TestCase {

	use DatabaseTestTrait;

	protected array $fixtures = [
		'plugin.TinyAuthBackend.TinyAuthRoles',
		'plugin.TinyAuthBackend.TinyAuthResources',
		'plugin.TinyAuthBackend.TinyAuthResourceAbilities',
		'plugin.TinyAuthBackend.TinyAuthResourceAcl',
	];

	public function setUp(): void {
		parent::setUp();

		Configure::write('TinyAuthBackend.roleHierarchy', false);
		Configure::write('TinyAuthBackend.multiRole', false);
		Configure::delete('TinyAuthBackend.superAdminRole');
		Configure::delete('TinyAuth.superAdminRole');

		$this->insertRow('tinyauth_roles', [
			'id' => 1,
			'name' => 'Administrator',
			'alias' => 'admin',
			'parent_id' => null,
			'sort_order' => 100,
		]);
		$this->insertRow('tinyauth_roles', [
			'id' => 2,
			'name' => 'Root',
			'alias' => 'root',
			'parent_id' => null,
			'sort_order' => 200,
		]);
		$this->insertRow('tinyauth_resources', [
			'id' => 1,
			'name' => 'Article',
			'entity_class' => 'TestApp\Model\Entity\Article',
			'table_name' => 'articles',
		]);
		$this->insertRow('tinyauth_resource_abilities', [
			'id' => 1,
			'resource_id' => 1,
			'name' => 'edit',
			'description' => null,
		]);
		$this->insertRow('tinyauth_resource_acl', [
			'id' => 1,
			'resource_ability_id' => 1,
			'role_id' => 1,
			'type' => 'allow',
			'scope_id' => null,
		]);
	}

	public function testCanEditUsesSyncedResourceName(): void {
		$policy = new TinyAuthPolicy();
		$identity = new TestIdentity(new Entity(['id' => 99, 'role_id' => 1]));
		$article = new Article(['id' => 5, 'user_id' => 99]);

		$result = $policy->canEdit($identity, $article);

		$this->assertTrue($result);
	}

	/**
	 * Null identity must deny every check — guards against a silent
	 * passthrough from missing authentication.
	 *
	 * @return void
	 */
	public function testCanEditDeniesNullIdentity(): void {
		$policy = new TinyAuthPolicy();

		$this->assertFalse($policy->canEdit(null, new Article(['id' => 5, 'user_id' => 99])));
		$this->assertFalse($policy->canView(null, new Article(['id' => 5, 'user_id' => 99])));
		$this->assertFalse($policy->canDelete(null, new Article(['id' => 5, 'user_id' => 99])));
	}

	/**
	 * Custom abilities via __call must also accept an identity and
	 * pass the unwrapped user to TinyAuthService.
	 *
	 * @return void
	 */
	/**
	 * Null identity forces the query to `1 = 0` — no rows leak to
	 * anonymous visitors even on list endpoints.
	 *
	 * @return void
	 */
	public function testScopeIndexDeniesNullIdentity(): void {
		$this->insertRow('tinyauth_roles', [
			'id' => 99,
			'name' => 'Dummy',
			'alias' => 'dummy',
			'parent_id' => null,
			'sort_order' => 9999,
		]);
		// A freshly-created table with a known schema avoids schema
		// introspection errors on test_app Tables we don't ship a
		// fixture for.
		$table = $this->fetchTable('TinyAuthBackend.Roles');
		$query = $table->find();

		$policy = new TinyAuthPolicy();
		$result = $policy->scopeIndex(null, $query);

		$this->assertStringContainsString('1 = 0', $result->sql());
	}

	/**
	 * Super-admin identity returns the query unchanged — no WHERE
	 * narrowing, full access.
	 *
	 * @return void
	 */
	public function testScopeIndexSuperAdminBypass(): void {
		Configure::write('TinyAuth.superAdminRole', 'admin');

		$policy = new TinyAuthPolicy();
		$identity = new TestIdentity(new Entity(['id' => 1, 'role_id' => 1]));
		$query = $this->fetchTable('TinyAuthBackend.Roles')->find();

		$result = $policy->scopeIndex($identity, $query);

		$this->assertSame($query, $result);
	}

	public function testCustomAbilityViaCallAcceptsIdentity(): void {
		$this->insertRow('tinyauth_resource_abilities', [
			'id' => 2,
			'resource_id' => 1,
			'name' => 'publish',
			'description' => null,
		]);
		$this->insertRow('tinyauth_resource_acl', [
			'id' => 2,
			'resource_ability_id' => 2,
			'role_id' => 1,
			'type' => 'allow',
			'scope_id' => null,
		]);

		$policy = new TinyAuthPolicy();
		$identity = new TestIdentity(new Entity(['id' => 99, 'role_id' => 1]));
		$article = new Article(['id' => 5, 'user_id' => 99]);

		$this->assertTrue($policy->canPublish($identity, $article));
	}

	public function testBeforeUsesConfiguredSuperAdminRole(): void {
		Configure::write('TinyAuth.superAdminRole', 'root');

		$policy = new TinyAuthPolicy();
		$identity = new TestIdentity(new Entity(['id' => 1, 'role_id' => 2]));

		$result = $policy->before($identity, new Article(), 'edit');

		$this->assertTrue($result);
	}

}
