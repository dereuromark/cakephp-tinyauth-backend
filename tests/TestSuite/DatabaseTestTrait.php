<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestSuite;

use Cake\Database\Driver\Postgres;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

trait DatabaseTestTrait {

	protected function insertRow(string $table, array $data): void {
		$tableObject = TableRegistry::getTableLocator()->get($table);
		$entity = $tableObject->newEntity($data, [
			'accessibleFields' => ['*' => true],
		]);
		$tableObject->saveOrFail($entity, ['checkRules' => false]);

		if (array_key_exists('id', $data)) {
			$this->syncPrimaryKeySequence($tableObject, $table);
		}
	}

	protected function countRows(string $table, array $conditions): int {
		return TableRegistry::getTableLocator()->get($table)->find()->where($conditions)->count();
	}

	protected function syncPrimaryKeySequence(Table $tableObject, string $table): void {
		$connection = $tableObject->getConnection();
<<<<<<< HEAD
		$driver = $connection->getDriver();
		if (!($driver instanceof Postgres)) {
=======
		if (!($connection->getDriver() instanceof Postgres)) {
>>>>>>> 9fc4af4 (Fix CI across databases and static checks)
			return;
		}
		if (!$tableObject->getSchema()->hasColumn('id')) {
			return;
		}

<<<<<<< HEAD
		$quotedTable = $driver->quoteIdentifier($table);
=======
		$quotedTable = $connection->quoteIdentifier($table);
>>>>>>> 9fc4af4 (Fix CI across databases and static checks)
		$connection->execute(sprintf(
			"SELECT setval(pg_get_serial_sequence('%s', 'id'), COALESCE((SELECT MAX(id) FROM %s), 1), true)",
			$table,
			$quotedTable,
		));
	}

}
