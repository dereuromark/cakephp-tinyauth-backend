<?php

use Migrations\BaseMigration;

class TinyAuthBackendDefault extends BaseMigration {

	/**
	 * Creates tables
	 *
	 * @return void
	 */
	public function up() {
		$this->table('tiny_auth_allow_rules')
			->addColumn('type', 'integer', [ // allow/deny
				'default' => null,
				'limit' => 2,
				'null' => false,
			])
			->addColumn('path', 'string', [ // Vendor/Cms.Management/Admin/Articles::view
				'default' => null,
				'limit' => 250,
				'null' => false,
			])
			->addColumn('created', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('modified', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->create();
		$this->table('tiny_auth_allow_rules')
			->addIndex(['path'], ['unique' => true])
			->save();

		$this->table('tiny_auth_acl_rules')
			->addColumn('type', 'integer', [ // allow/deny
				'default' => null,
				'limit' => 2,
				'null' => false,
			])
			->addColumn('role', 'string', [ // admin, user, ...
				'default' => null,
				'limit' => 50,
				'null' => false,
			])
			->addColumn('path', 'string', [ // Vendor/Cms.Management/Admin/Articles::view
				'default' => null,
				'limit' => 250,
				'null' => false,
			])
			->addColumn('created', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->addColumn('modified', 'datetime', [
				'default' => null,
				'limit' => null,
				'null' => true,
			])
			->create();
		$this->table('tiny_auth_acl_rules')
			->addIndex(['path', 'role'], ['unique' => true, 'name' => 'path-role'])
			->save();
	}

	/**
	 * Drops tables
	 *
	 * @return void
	 */
	public function down() {
		$this->dropTable('tiny_auth_allow_rules');
		$this->dropTable('tiny_auth_acl_rules');
	}

}
