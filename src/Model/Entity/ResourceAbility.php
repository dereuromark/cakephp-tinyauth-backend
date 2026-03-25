<?php
declare(strict_types=1);

namespace TinyAuthBackend\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property int $resource_id
 * @property string $name
 * @property string|null $description
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property \TinyAuthBackend\Model\Entity\Resource|null $resource
 * @property array<\TinyAuthBackend\Model\Entity\ResourceAcl> $resource_acl
 */
class ResourceAbility extends Entity {

	/**
	 * @var array<string, bool>
	 */
	protected array $_accessible = [
		'resource_id' => true,
		'name' => true,
		'description' => true,
		'created' => true,
		'modified' => true,
		'resource' => true,
		'resource_acl' => true,
	];

}
