<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase\Service;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use TinyAuthBackend\Service\ResourceSyncService;
use TinyAuthBackend\Test\TestSuite\DatabaseTestTrait;

class ResourceSyncServiceTest extends TestCase {

	use DatabaseTestTrait;

	protected array $fixtures = [
		'plugin.TinyAuthBackend.TinyAuthResources',
		'plugin.TinyAuthBackend.TinyAuthResourceAbilities',
	];

	public function setUp(): void {
		parent::setUp();

		$this->loadPlugins(['TinyAuthBackend']);
	}

	public function tearDown(): void {
		Configure::delete('TinyAuthBackend.excludedPlugins');

		parent::tearDown();
	}

	public function testScanExcludesConfiguredPluginsByDefault(): void {
		$result = (new ResourceSyncService())->scan();

		$this->assertSame([], $result);
	}

	public function testScanIncludesPluginEntitiesWhenExclusionsAreDisabled(): void {
		Configure::write('TinyAuthBackend.excludedPlugins', []);

		$result = (new ResourceSyncService())->scan();
		$entityClasses = array_column($result, 'entity_class');

		$this->assertContains('TinyAuthBackend\\Model\\Entity\\Role', $entityClasses);
	}

	public function testSyncCreatesResourcesAndAbilitiesWhenExclusionsAreDisabled(): void {
		Configure::write('TinyAuthBackend.excludedPlugins', []);

		$result = (new ResourceSyncService())->sync();

		$this->assertGreaterThan(0, $result['added']);
		$this->assertGreaterThan(0, $result['abilities_added']);
		$this->assertGreaterThan(0, $this->countRows('tinyauth_resources', []));
		$this->assertGreaterThan(0, $this->countRows('tinyauth_resource_abilities', []));
	}

}
