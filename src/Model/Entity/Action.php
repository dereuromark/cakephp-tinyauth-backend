<?php
declare(strict_types=1);

namespace TinyAuthBackend\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property int $controller_id
 * @property string $name
 * @property bool $is_public
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property \TinyAuthBackend\Model\Entity\TinyauthController|null $tinyauth_controller
 * @property array<\TinyAuthBackend\Model\Entity\AclPermission> $acl_permissions
 * @property-read string $full_path
 */
class Action extends Entity {

	/**
	 * @var array<string, bool>
	 */
	protected array $_accessible = [
		'controller_id' => true,
		'name' => true,
		'is_public' => true,
		'created' => true,
		'modified' => true,
		'tinyauth_controller' => true,
		'acl_permissions' => true,
	];

	/**
	 * @return string
	 */
	protected function _getFullPath(): string {
		$controller = $this->tinyauth_controller;
		if (!$controller) {
			return $this->name;
		}

		return $controller->full_path . '::' . $this->name;
	}

}
