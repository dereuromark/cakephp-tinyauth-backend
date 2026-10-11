<?php
declare(strict_types=1);

namespace TinyAuthBackend\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use TinyAuthBackend\Utility\AdapterConfig;
use TinyAuthBackend\Utility\Importer;

class ImportCommand extends Command {

	protected bool $importFailed = false;

	/**
	 * @inheritDoc
	 */
	public function execute(Arguments $args, ConsoleIo $io): ?int {
		$this->importFailed = false;
		$type = $args->getArgument('type');
		$file = $args->getArgument('file');
		if (!$type || $type === 'allow') {
			$this->importAllow($file, $io);
		}
		if (!$type || $type === 'acl') {
			$this->importAcl($file, $io);
		}

		return $this->importFailed ? static::CODE_ERROR : static::CODE_SUCCESS;
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
	 * @param \Cake\Console\ConsoleIo $io
	 * @return void
	 */
	protected function importAllow(?string $file, ConsoleIo $io): void {
		if (!AdapterConfig::isAllowEnabled()) {
			$this->importFailed = true;
			$io->error('Allow not enabled, skipping');

			return;
		}

		if ($file) {
			$fileName = pathinfo($file, PATHINFO_BASENAME);
		} else {
			$fileName = Configure::read('TinyAuth.allowFile') ?: 'auth_allow.ini';
			$path = Configure::read('TinyAuth.allowFilePath') ?: ROOT . DS . 'config' . DS;
			$file = rtrim($path, '/\\') . DS . $fileName;
		}
		if (!is_file($file) || !is_readable($file)) {
			$this->importFailed = true;
			$io->error($fileName . ' does not exist or cannot be found, skipping');

			return;
		}

		$importer = new Importer();
		$importer->importAllow($file);

		$io->success('Imported: ' . $fileName);
	}

	/**
	 * @param string|null $file
	 * @param \Cake\Console\ConsoleIo $io
	 * @return void
	 */
	protected function importAcl(?string $file, ConsoleIo $io): void {
		if (!AdapterConfig::isAclEnabled()) {
			$this->importFailed = true;
			$io->error('ACL not enabled, skipping');

			return;
		}

		if ($file) {
			$fileName = pathinfo($file, PATHINFO_BASENAME);
		} else {
			$fileName = Configure::read('TinyAuth.aclFile') ?: 'auth_acl.ini';
			$path = Configure::read('TinyAuth.aclFilePath') ?: ROOT . DS . 'config' . DS;
			$file = rtrim($path, '/\\') . DS . $fileName;
		}
		if (!is_file($file) || !is_readable($file)) {
			$this->importFailed = true;
			$io->error($fileName . ' does not exist or cannot be found, skipping');

			return;
		}

		$importer = new Importer();
		$importer->importAcl($file);

		$io->success('Imported: ' . $fileName);
	}

}
