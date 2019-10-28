<?php
use Migrations\AbstractMigration;

class TinyAuthBackendDefault extends AbstractMigration
{
	/**
	 * Creates tables
	 *
	 * @return void
	 */
	public function up()
	{
		$this->table('tinyauth_allow_rules')
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

		$this->table('tinyauth_acl_rules')
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
	}

	/**
	 * Drops tables
	 *
	 * @return void
	 */
	public function down()
	{
		$this->dropTable('tinyauth_allow_rules');
		$this->dropTable('tinyauth_acl_rules');
	}
}
