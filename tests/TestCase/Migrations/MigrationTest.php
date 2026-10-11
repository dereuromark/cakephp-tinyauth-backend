<?php
declare(strict_types=1);

namespace TinyAuthBackend\Test\TestCase\Migrations;

use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\TestCase;
use Migrations\Migrations;

class MigrationTest extends TestCase {

	public function testFreshDatabaseCanStoreBackendPermissions(): void {
		ConnectionManager::setConfig('backend_migration_test', [
			'className' => 'Cake\\Database\\Connection',
			'driver' => 'Cake\\Database\\Driver\\Sqlite',
			'database' => ':memory:',
		]);
		try {
			$migrations = new Migrations(['connection' => 'backend_migration_test', 'plugin' => 'TinyAuthBackend']);
			$this->assertTrue($migrations->migrate());
			$connection = ConnectionManager::get('backend_migration_test');
			$tables = $connection->getSchemaCollection()->listTables();
			$this->assertContains('tinyauth_resource_acl', $tables);
			$this->assertContains('tinyauth_scopes', $tables);
			$this->assertTrue($connection->getSchemaCollection()->describe('tinyauth_acl_permissions')->hasColumn('description'));
			$connection->insert('tinyauth_roles', ['name' => 'Admin', 'alias' => 'admin', 'sort_order' => 1]);
			$this->assertSame(1, $connection->execute('SELECT COUNT(*) FROM tinyauth_roles')->fetchColumn(0));
		} finally {
			ConnectionManager::drop('backend_migration_test');
		}
	}

}
