<?php
declare(strict_types=1);

namespace TinyAuthBackend\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property string $name
 * @property string $alias
 * @property int|null $parent_id
 * @property int $sort_order
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property \TinyAuthBackend\Model\Entity\Role|null $parent
 * @property array<\TinyAuthBackend\Model\Entity\Role> $children
 * @property array<\TinyAuthBackend\Model\Entity\AclPermission> $acl_permissions
 * @property array<\TinyAuthBackend\Model\Entity\ResourceAcl> $resource_acl
 * @property-read bool $is_root
 */
class Role extends Entity {

	/**
	 * @var array<string, bool>
	 */
	protected array $_accessible = [
		'name' => true,
		'alias' => true,
		'parent_id' => true,
		'sort_order' => true,
		'created' => true,
		'modified' => true,
		'parent' => true,
		'children' => true,
		'acl_permissions' => true,
		'resource_acl' => true,
	];

	/**
	 * @return bool
	 */
	protected function _getIsRoot(): bool {
		return $this->parent_id === null;
	}

}
