<?php

namespace TinyAuthBackend\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Routing\Router;
use TinyAuthBackend\Utility\AdapterConfig;
use TinyAuthBackend\Utility\Importer;
use TinyAuth\Utility\TinyAuth;

/**
 * @property \TinyAuthBackend\Model\Table\AllowRulesTable $AllowRules
 */
class TinyAuthBackendShell extends Shell {

	/**
	 * @var string
	 */
	public $modelClass = 'TinyAuthBackend.AllowRules';

	/**
	 * @param string $role
	 *
	 * @return void
	 */
	public function init($role) {
		$availableRoles = $this->_getAvailableRoles();
		if (!in_array($role, $availableRoles, true)) {
			$this->abort('Role ' . $role . ' does not seem to exist. Available: ' . implode(', ', $availableRoles));
		}

		$importer = new Importer();
		$importer->initializeAcl($role);

		$url = Router::url(['plugin' => 'TinyAuthBackend', 'prefix' => 'admin', 'controller' => 'Auth', 'action' => 'index'], true);

		$this->success('Necessary ACL rules stored. Using a user with this `' . $role . '` role you can now navigate to the backend `' . $url . '`.');
	}

	/**
	 * @return string[]
	 */
	protected function _getAvailableRoles() {
		$roles = (new TinyAuth())->getAvailableRoles();

		return array_keys($roles);
	}

	/**
	 * @param string|null $type
	 * @param string|null $file
	 *
	 * @return void
	 */
	public function import($type = null, $file = null) {
		if (!$type || $type === 'allow') {
			$this->importAllow($file);
		}
		if (!$type || $type === 'acl') {
			$this->importAcl($file);
		}
	}

	/**
	 * @return \Cake\Console\ConsoleOptionParser
	 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();
		$parser->setDescription(
			'Use TinyAuth backend functionality.'
		);

		$initParser = [
			'arguments' => [
				'role' => [
					'help' => 'Slug of role to be used to open backend for',
					'required' => true,
				],
			],
		];
		$parser->addSubcommand('init', [
			'help' => 'Initialize the backend by allowing the given admin role access.',
			'parser' => $initParser,
		]);

		$importParser = [
			'arguments' => [
				'type' => [
					'help' => 'Type of INI file to import (allow/acl). Defaults to both.',
					'choices' => ['allow', 'acl'],
				],
			],
		];
		$parser->addSubcommand('import', [
			'help' => 'Import existing rules from INI files.',
			'parser' => $importParser,
		]);

		return $parser;
	}

	/**
	 * @param string|null $file
	 *
	 * @return void
	 */
	protected function importAllow($file) {
		if (!AdapterConfig::isAllowEnabled()) {
			$this->err('Allow not enabled, skipping');
			return;
		}

		if ($file) {
			$fileName = pathinfo($file, PATHINFO_BASENAME);
		} else {
			$fileName = Configure::read('TinyAuth.allowFile');
			$path = Configure::read('TinyAuth.allowFilePath') ?: ROOT . DS . 'config' . DS;
			$file = $path . $fileName;
		}
		if (!file_exists($file)) {
			$this->err($fileName . ' does not exist or cannot be found, skipping');
			return;
		}

		$importer = new Importer();
		$importer->importAllow($file);

		$this->success('Imported: ' . $fileName);
	}

	/**
	 * @param string|null $file
	 *
	 * @return void
	 */
	protected function importAcl($file) {
		if (!AdapterConfig::isAclEnabled()) {
			$this->err('ACL not enabled, skipping');
			return;
		}

		if ($file) {
			$fileName = pathinfo($file, PATHINFO_BASENAME);
		} else {
			$fileName = Configure::read('TinyAuth.aclFile');
			$path = Configure::read('TinyAuth.aclFilePath') ?: ROOT . DS . 'config' . DS;
			$file = $path . $fileName;
		}
		if (!file_exists($file)) {
			$this->err($fileName . ' does not exist or cannot be found, skipping');
			return;
		}

		$importer = new Importer();
		$importer->importAcl($file);

		$this->success('Imported: ' . $fileName);
	}

}
