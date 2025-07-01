<?php

namespace TinyAuthBackend\Command;

use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Datasource\ModelAwareTrait;
use Shim\Command\Command;
use TinyAuthBackend\Utility\AdapterConfig;
use TinyAuthBackend\Utility\Importer;

/**
 * @property \TinyAuthBackend\Model\Table\AllowRulesTable $AllowRules
 */
class ImportCommand extends Command {

	use ModelAwareTrait;

	/**
	 * @var string|null
	 */
	protected ?string $modelClass = 'TinyAuthBackend.AllowRules';

	/**
	 * @inheritDoc
	 */
	public function execute(Arguments $args, ConsoleIo $io): ?int {
		$type = $args->getArgument('type');
		$file = $args->getArgument('file');
		if (!$type || $type === 'allow') {
			$this->importAllow($file);
		}
		if (!$type || $type === 'acl') {
			$this->importAcl($file);
		}

		return static::CODE_SUCCESS;
	}

	/**
	 * @return \Cake\Console\ConsoleOptionParser
	 */
	public function getOptionParser(): ConsoleOptionParser {
		$parser = parent::getOptionParser();
		$parser->setDescription(
			'Import existing rules from INI files.',
		);

		$parser->addArgument('type', [
			'help' => 'Type of INI file to import (allow/acl). Defaults to both.',
			'choices' => ['allow', 'acl'],
		]);
		$parser->addArgument('file', [
			'help' => 'File name',
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
			$this->io->error('Allow not enabled, skipping');

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
			$this->io->error($fileName . ' does not exist or cannot be found, skipping');

			return;
		}

		$importer = new Importer();
		$importer->importAllow($file);

		$this->io->success('Imported: ' . $fileName);
	}

	/**
	 * @param string|null $file
	 *
	 * @return void
	 */
	protected function importAcl($file) {
		if (!AdapterConfig::isAclEnabled()) {
			$this->io->error('ACL not enabled, skipping');

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
			$this->io->error($fileName . ' does not exist or cannot be found, skipping');

			return;
		}

		$importer = new Importer();
		$importer->importAcl($file);

		$this->io->success('Imported: ' . $fileName);
	}

}
