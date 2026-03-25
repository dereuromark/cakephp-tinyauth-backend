<?php
declare(strict_types=1);

namespace TinyAuthBackend\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property string|null $plugin
 * @property string|null $prefix
 * @property string $name
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property array<\TinyAuthBackend\Model\Entity\Action> $actions
 * @property-read string $full_path
 */
class TinyauthController extends Entity {

	/**
	 * @var array<string, bool>
	 */
	protected array $_accessible = [
		'plugin' => true,
		'prefix' => true,
		'name' => true,
		'created' => true,
		'modified' => true,
		'actions' => true,
	];

	/**
	 * @return string
	 */
	protected function _getFullPath(): string {
		$parts = [];
		if ($this->plugin) {
			$parts[] = $this->plugin . '.';
		}
		if ($this->prefix) {
			$parts[] = $this->prefix . '/';
		}
		$parts[] = $this->name;

		return implode('', $parts);
	}

}
