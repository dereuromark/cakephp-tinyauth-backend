<?php
declare(strict_types=1);

namespace TinyAuthBackend\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property int $resource_ability_id
 * @property int $role_id
 * @property string $type
 * @property int|null $scope_id
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property \TinyAuthBackend\Model\Entity\ResourceAbility|null $resource_ability
 * @property \TinyAuthBackend\Model\Entity\Role|null $role
 * @property \TinyAuthBackend\Model\Entity\Scope|null $scope
 */
class ResourceAcl extends Entity {

	/**
	 * @var string
	 */
	public const TYPE_ALLOW = 'allow';

	/**
	 * @var string
	 */
	public const TYPE_DENY = 'deny';

	/**
	 * @var array<string, bool>
	 */
	protected array $_accessible = [
		'resource_ability_id' => true,
		'role_id' => true,
		'type' => true,
		'scope_id' => true,
		'created' => true,
		'modified' => true,
		'resource_ability' => true,
		'role' => true,
		'scope' => true,
	];

	/**
	 * @return array<string, string>
	 */
	public static function types(): array {
		return [
			static::TYPE_ALLOW => __('Allow'),
			static::TYPE_DENY => __('Deny'),
		];
	}

}
