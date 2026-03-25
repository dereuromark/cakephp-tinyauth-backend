<?php

namespace TinyAuthBackend\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TinyAuthAclPermissionsFixture
 */
class TinyAuthAclPermissionsFixture extends TestFixture {

	/**
	 * Table name
	 *
	 * @var string
	 */
	public string $table = 'tinyauth_acl_permissions';

	/**
	 * Fields
	 *
	 * @var array
	 */
	// @codingStandardsIgnoreStart
	public array $fields = [
		'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
		'action_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
		'role_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
		'type' => ['type' => 'string', 'length' => 10, 'null' => false, 'default' => 'allow', 'comment' => '', 'precision' => null, 'fixed' => null],
		'created' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
		'modified' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
		'_constraints' => [
			'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
			'action_id_role_id_unique' => ['type' => 'unique', 'columns' => ['action_id', 'role_id'], 'length' => []],
		],
		'_options' => [
			'engine' => 'InnoDB',
			'collation' => 'utf8_unicode_ci',
		],
	];
	// @codingStandardsIgnoreEnd

	/**
	 * Init method
	 *
	 * @return void
	 */
	public function init(): void {
		$this->records = [];
		parent::init();
	}

}
