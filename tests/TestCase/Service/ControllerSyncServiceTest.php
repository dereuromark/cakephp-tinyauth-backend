<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase\Service;

use Cake\ORM\Exception\PersistenceFailedException;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use TinyAuthBackend\Service\ControllerSyncService;
use TinyAuthBackend\Test\TestSuite\DatabaseTestTrait;

class ControllerSyncServiceTest extends TestCase {

	use DatabaseTestTrait;

	protected array $fixtures = [
		'plugin.TinyAuthBackend.TinyAuthControllers',
		'plugin.TinyAuthBackend.TinyAuthActions',
	];

	public function setUp(): void {
		parent::setUp();

		$this->loadPlugins(['TinyAuthBackend']);
	}

	public function testScanUsesNullPluginForAppControllers(): void {
		$service = new class extends ControllerSyncService {

			/**
			 * @var array<array{path: string, plugin: string|null, prefix: string|null}>
			 */
			public array $calls = [];

			protected function scanPath(string $path, ?string $plugin, ?string $prefix = null): array {
				$this->calls[] = compact('path', 'plugin', 'prefix');

				return [];
			}
		};

		$service->scan();

		$this->assertSame(APP . 'Controller' . DS, $service->calls[0]['path']);
		$this->assertNull($service->calls[0]['plugin']);
	}

	public function testSyncNormalizesLegacyAppNamespaceWithoutChangingControllerId(): void {
		$this->insertRow('tinyauth_controllers', [
			'id' => 42,
			'plugin' => 'TestApp',
			'prefix' => null,
			'name' => 'Users',
		]);
		$this->insertRow('tinyauth_actions', [
			'id' => 7,
			'controller_id' => 42,
			'name' => 'index',
			'is_public' => true,
		]);
		$service = new class extends ControllerSyncService {

			public function scan(): array {
				return [
					[
						'plugin' => null,
						'prefix' => null,
						'name' => 'Users',
						'actions' => ['index'],
					],
				];
			}
		};

		$result = $service->sync();

		$this->assertSame(['added' => 0, 'updated' => 1, 'actions_added' => 0], $result);
		$controller = TableRegistry::getTableLocator()->get('TinyAuthBackend.TinyauthControllers')->get(42);
		$this->assertNull($controller->plugin);
		$this->assertSame(1, $this->countRows('tinyauth_controllers', []));
		$this->assertSame(1, $this->countRows('tinyauth_actions', ['controller_id' => 42]));
	}

	public function testSyncStopsWhenLegacyNormalizationFails(): void {
		$this->insertRow('tinyauth_controllers', [
			'id' => 42,
			'plugin' => 'TestApp',
			'prefix' => null,
			'name' => 'Users',
		]);
		$controllersTable = TableRegistry::getTableLocator()->get('TinyAuthBackend.TinyauthControllers');
		$controllersTable->getEventManager()->on('Model.beforeSave', function ($event, $entity): void {
			if ($entity->id === 42) {
				$event->stopPropagation();
			}
		});
		$service = new class extends ControllerSyncService {

			public function scan(): array {
				return [
					[
						'plugin' => null,
						'prefix' => null,
						'name' => 'Users',
						'actions' => [],
					],
				];
			}
		};

		try {
			$service->sync();
			$this->fail('Expected legacy normalization to fail.');
		} catch (PersistenceFailedException) {
			$this->assertSame(1, $this->countRows('tinyauth_controllers', []));
			$this->assertSame(0, $this->countRows('tinyauth_controllers', ['plugin IS' => null]));
		}
	}

}
