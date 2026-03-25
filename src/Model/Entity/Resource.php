<?php
declare(strict_types=1);

namespace TinyAuthBackend\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property string $name
 * @property string $entity_class
 * @property string $table_name
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property array<\TinyAuthBackend\Model\Entity\ResourceAbility> $resource_abilities
 */
class Resource extends Entity {

	/**
	 * @var array<string, bool>
	 */
	protected array $_accessible = [
		'name' => true,
		'entity_class' => true,
		'table_name' => true,
		'created' => true,
		'modified' => true,
		'resource_abilities' => true,
	];

}
