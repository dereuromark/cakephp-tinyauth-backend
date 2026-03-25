<?php

namespace TinyAuthBackend\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TinyAuthResourcesFixture
 */
class TinyAuthResourcesFixture extends TestFixture {

	/**
	 * Table name
	 *
	 * @var string
	 */
	public string $table = 'tinyauth_resources';

	/**
	 * Fields
	 *
	 * @var array
	 */
	// @codingStandardsIgnoreStart
	public array $fields = [
		'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
		'name' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
		'entity_class' => ['type' => 'string', 'length' => 200, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
		'table_name' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'fixed' => null],
		'created' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
		'modified' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
		'_constraints' => [
			'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
			'tinyauth_resources_name_unique' => ['type' => 'unique', 'columns' => ['name'], 'length' => []],
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
