<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase\Command;

use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;
use TinyAuthBackend\Test\TestSuite\DatabaseTestTrait;

class SyncCommandTest extends TestCase {

	use ConsoleIntegrationTestTrait;
	use DatabaseTestTrait;

	protected array $fixtures = [
		'plugin.TinyAuthBackend.TinyAuthControllers',
		'plugin.TinyAuthBackend.TinyAuthActions',
		'plugin.TinyAuthBackend.TinyAuthResources',
		'plugin.TinyAuthBackend.TinyAuthResourceAbilities',
	];

	public function setUp(): void {
		parent::setUp();

		$this->loadPlugins(['TinyAuthBackend']);
	}

	public function testSyncWithoutArgsRunsBothSubcommands(): void {
		$this->exec('tiny_auth_backend sync');

		$this->assertExitSuccess();
		$this->assertOutputContains('Controllers synced:');
		$this->assertOutputContains('Resources synced:');
	}

	public function testSyncControllersOnlySkipsResources(): void {
		$this->exec('tiny_auth_backend sync controllers');

		$this->assertExitSuccess();
		$this->assertOutputContains('Controllers synced:');
		$this->assertOutputNotContains('Resources synced:');
	}

	public function testSyncResourcesOnlySkipsControllers(): void {
		$this->exec('tiny_auth_backend sync resources');

		$this->assertExitSuccess();
		$this->assertOutputContains('Resources synced:');
		$this->assertOutputNotContains('Controllers synced:');
	}

	public function testSyncRejectsUnknownType(): void {
		$this->exec('tiny_auth_backend sync garbage');

		$this->assertExitError();
	}

	public function testSyncIsIdempotent(): void {
		$this->exec('tiny_auth_backend sync');
		$this->assertExitSuccess();

		$controllersAfterFirstRun = $this->countRows('tinyauth_controllers', []);
		$actionsAfterFirstRun = $this->countRows('tinyauth_actions', []);

		$this->exec('tiny_auth_backend sync');
		$this->assertExitSuccess();

		$this->assertSame(
			$controllersAfterFirstRun,
			$this->countRows('tinyauth_controllers', []),
			'Re-running sync must not create duplicate controller rows.',
		);
		$this->assertSame(
			$actionsAfterFirstRun,
			$this->countRows('tinyauth_actions', []),
			'Re-running sync must not create duplicate action rows.',
		);
	}

}
