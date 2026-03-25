<?php
declare(strict_types=1);

namespace TinyAuthBackend\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string $entity_field
 * @property string $user_field
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property-read string $condition_preview
 */
class Scope extends Entity {

	/**
	 * @var array<string, bool>
	 */
	protected array $_accessible = [
		'name' => true,
		'description' => true,
		'entity_field' => true,
		'user_field' => true,
		'created' => true,
		'modified' => true,
	];

	/**
	 * @return string
	 */
	protected function _getConditionPreview(): string {
		return sprintf('entity.%s = user.%s', $this->entity_field, $this->user_field);
	}

}
