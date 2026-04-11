<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateTinyAuthTables extends BaseMigration {

	/**
	 * @return void
	 */
	public function change(): void {
		// Drop legacy 2.x tables from previous versions
		if ($this->hasTable('tiny_auth_acl_rules')) {
			$this->table('tiny_auth_acl_rules')->drop()->save();
		}
		if ($this->hasTable('tiny_auth_allow_rules')) {
			$this->table('tiny_auth_allow_rules')->drop()->save();
		}

		// Roles table
		$this->table('tinyauth_roles')
			->addColumn('name', 'string', ['limit' => 100, 'null' => false])
			->addColumn('alias', 'string', ['limit' => 50, 'null' => false])
			->addColumn('parent_id', 'integer', ['null' => true, 'default' => null])
			->addColumn('sort_order', 'integer', ['default' => 0])
			->addColumn('created', 'datetime', ['null' => true])
			->addColumn('modified', 'datetime', ['null' => true])
			->addIndex(['alias'], ['unique' => true])
			->addIndex(['parent_id'])
			->create();

		// Add self-referencing foreign key after table creation
		$this->table('tinyauth_roles')
			->addForeignKey('parent_id', 'tinyauth_roles', 'id', [
				'delete' => 'SET_NULL',
				'update' => 'CASCADE',
			])
			->update();

		// Controllers table
		$this->table('tinyauth_controllers')
			->addColumn('plugin', 'string', ['limit' => 100, 'null' => true])
			->addColumn('prefix', 'string', ['limit' => 100, 'null' => true])
			->addColumn('name', 'string', ['limit' => 100, 'null' => false])
			->addColumn('created', 'datetime', ['null' => true])
			->addColumn('modified', 'datetime', ['null' => true])
			->addIndex(['plugin', 'prefix', 'name'], ['unique' => true])
			->create();

		// Actions table
		$this->table('tinyauth_actions')
			->addColumn('controller_id', 'integer', ['null' => false])
			->addColumn('name', 'string', ['limit' => 100, 'null' => false])
			->addColumn('is_public', 'boolean', ['default' => false])
			->addColumn('created', 'datetime', ['null' => true])
			->addColumn('modified', 'datetime', ['null' => true])
			->addIndex(['controller_id', 'name'], ['unique' => true])
			->addForeignKey('controller_id', 'tinyauth_controllers', 'id', [
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
			])
			->create();

		// ACL Permissions table
		$this->table('tinyauth_acl_permissions')
			->addColumn('action_id', 'integer', ['null' => false])
			->addColumn('role_id', 'integer', ['null' => false])
			->addColumn('type', 'string', ['limit' => 10, 'default' => 'allow'])
			->addColumn('created', 'datetime', ['null' => true])
			->addColumn('modified', 'datetime', ['null' => true])
			->addIndex(['action_id', 'role_id'], ['unique' => true])
			->addForeignKey('action_id', 'tinyauth_actions', 'id', [
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
			])
			->addForeignKey('role_id', 'tinyauth_roles', 'id', [
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
			])
			->create();

		// Resources table
		$this->table('tinyauth_resources')
			->addColumn('name', 'string', ['limit' => 100, 'null' => false])
			->addColumn('entity_class', 'string', ['limit' => 200, 'null' => false])
			->addColumn('table_name', 'string', ['limit' => 100, 'null' => false])
			->addColumn('created', 'datetime', ['null' => true])
			->addColumn('modified', 'datetime', ['null' => true])
			->addIndex(['entity_class'], ['unique' => true])
			->create();

		// Resource Abilities table
		$this->table('tinyauth_resource_abilities')
			->addColumn('resource_id', 'integer', ['null' => false])
			->addColumn('name', 'string', ['limit' => 50, 'null' => false])
			->addColumn('description', 'string', ['limit' => 200, 'null' => true])
			->addColumn('created', 'datetime', ['null' => true])
			->addColumn('modified', 'datetime', ['null' => true])
			->addIndex(['resource_id', 'name'], ['unique' => true])
			->addForeignKey('resource_id', 'tinyauth_resources', 'id', [
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
			])
			->create();

		// Scopes table
		$this->table('tinyauth_scopes')
			->addColumn('name', 'string', ['limit' => 50, 'null' => false])
			->addColumn('description', 'string', ['limit' => 200, 'null' => true])
			->addColumn('entity_field', 'string', ['limit' => 100, 'null' => false])
			->addColumn('user_field', 'string', ['limit' => 100, 'null' => false])
			->addColumn('created', 'datetime', ['null' => true])
			->addColumn('modified', 'datetime', ['null' => true])
			->addIndex(['name'], ['unique' => true])
			->create();

		// Resource ACL table
		$this->table('tinyauth_resource_acl')
			->addColumn('resource_ability_id', 'integer', ['null' => false])
			->addColumn('role_id', 'integer', ['null' => false])
			->addColumn('type', 'string', ['limit' => 10, 'default' => 'allow'])
			->addColumn('scope_id', 'integer', ['null' => true])
			->addColumn('created', 'datetime', ['null' => true])
			->addColumn('modified', 'datetime', ['null' => true])
			->addIndex(['resource_ability_id', 'role_id'], ['unique' => true])
			->addForeignKey('resource_ability_id', 'tinyauth_resource_abilities', 'id', [
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
			])
			->addForeignKey('role_id', 'tinyauth_roles', 'id', [
				'delete' => 'CASCADE',
				'update' => 'CASCADE',
			])
			->addForeignKey('scope_id', 'tinyauth_scopes', 'id', [
				'delete' => 'SET_NULL',
				'update' => 'CASCADE',
			])
			->create();
	}

}
