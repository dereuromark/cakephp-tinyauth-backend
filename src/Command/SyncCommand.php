<?php
declare(strict_types=1);

namespace TinyAuthBackend\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use TinyAuthBackend\Service\ControllerSyncService;
use TinyAuthBackend\Service\ResourceSyncService;

/**
 * Sync command: scans the application for controllers, actions, and
 * resource entities, and writes discovered rows into the normalized
 * TinyAuth tables.
 *
 * This is the CLI equivalent of the "Sync" buttons in the admin UI at
 * `/admin/auth/sync`. It's intended for CI and deploy scripts so
 * permission state stays aligned with code changes automatically,
 * without a human having to click through the web UI after every
 * release.
 *
 * Usage:
 *
 * ```bash
 * bin/cake tiny_auth_backend sync # both controllers and resources
 * bin/cake tiny_auth_backend sync controllers # only controllers + actions
 * bin/cake tiny_auth_backend sync resources # only entity resources + abilities
 * ```
 *
 * Sync is idempotent — re-running it never clobbers existing grants.
 * Controllers and actions that appear in code are added; existing
 * rows are left alone. Orphans (rows whose controllers no longer
 * exist) are not auto-pruned.
 */
class SyncCommand extends Command {

	/**
	 * @inheritDoc
	 */
	public function execute(Arguments $args, ConsoleIo $io): ?int {
		$type = $args->getArgument('type');

		if (!$type || $type === 'controllers') {
			$this->syncControllers($io);
		}
		if (!$type || $type === 'resources') {
			$this->syncResources($io);
		}

		return static::CODE_SUCCESS;
	}

	/**
	 * @return \Cake\Console\ConsoleOptionParser
	 */
	public function getOptionParser(): ConsoleOptionParser {
		$parser = parent::getOptionParser();
		$parser->setDescription(
			'Sync controllers, actions, and entity resources from code into the TinyAuth backend.',
		);

		$parser->addArgument('type', [
			'help' => 'What to sync (controllers/resources). Defaults to both.',
			'choices' => ['controllers', 'resources'],
		]);

		return $parser;
	}

	/**
	 * @param \Cake\Console\ConsoleIo $io
	 * @return void
	 */
	protected function syncControllers(ConsoleIo $io): void {
		$result = (new ControllerSyncService())->sync();

		$io->success(sprintf(
			'Controllers synced: %d added, %d updated, %d actions added',
			$result['added'],
			$result['updated'],
			$result['actions_added'],
		));
	}

	/**
	 * @param \Cake\Console\ConsoleIo $io
	 * @return void
	 */
	protected function syncResources(ConsoleIo $io): void {
		$result = (new ResourceSyncService())->sync();

		$io->success(sprintf(
			'Resources synced: %d added, %d abilities added',
			$result['added'],
			$result['abilities_added'],
		));
	}

}
