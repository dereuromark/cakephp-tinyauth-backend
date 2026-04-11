<?php
declare(strict_types=1);

namespace TinyAuthBackend\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property int $action_id
 * @property int $role_id
 * @property string $type
 * @property string|null $description
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property \TinyAuthBackend\Model\Entity\Action|null $action
 * @property \TinyAuthBackend\Model\Entity\Role|null $role
 */
class AclPermission extends Entity {

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
		'action_id' => true,
		'role_id' => true,
		'type' => true,
		'description' => true,
		'created' => true,
		'modified' => true,
		'action' => true,
		'role' => true,
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
