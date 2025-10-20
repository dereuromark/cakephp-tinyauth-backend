<?php

namespace TinyAuthBackend\Command;

use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Datasource\ModelAwareTrait;
use Cake\Routing\Router;
use Shim\Command\Command;
use TinyAuth\Utility\TinyAuth;
use TinyAuthBackend\Utility\Importer;

/**
 * @property \TinyAuthBackend\Model\Table\AllowRulesTable $AllowRules
 */
class InitCommand extends Command {

	use ModelAwareTrait;

	/**
	 * @inheritDoc
	 */
	public function __construct() {
		parent::__construct();
		$this->modelClass = 'TinyAuthBackend.AllowRules';
	}

	/**
	 * @inheritDoc
	 */
	public function execute(Arguments $args, ConsoleIo $io): ?int {
		$role = $args->getArgument('role');
		$availableRoles = $this->_getAvailableRoles();
		if (!in_array($role, $availableRoles, true)) {
			$this->io->abort('Role ' . $role . ' does not seem to exist. Available: ' . implode(', ', $availableRoles));
		}

		$importer = new Importer();
		$importer->initializeAcl($role);

		$url = Router::url(['plugin' => 'TinyAuthBackend', 'prefix' => 'Admin', 'controller' => 'Auth', 'action' => 'index'], true);

		$this->io->success('Necessary ACL rules stored. Using a user with this `' . $role . '` role you can now navigate to the backend `' . $url . '`.');

		return static::CODE_SUCCESS;
	}

	/**
	 * @return array<string>
	 */
	protected function _getAvailableRoles() {
		$roles = (new TinyAuth())->getAvailableRoles();

		return array_keys($roles);
	}

	/**
	 * @return \Cake\Console\ConsoleOptionParser
	 */
	public function getOptionParser(): ConsoleOptionParser {
		$parser = parent::getOptionParser();
		$parser->setDescription(
			'Initialize the backend by allowing the given admin role access.',
		);

		$parser->addArgument('role', [
			'help' => 'Slug of role to be used to open backend for',
			'required' => true,
		]);

		return $parser;
	}

}
