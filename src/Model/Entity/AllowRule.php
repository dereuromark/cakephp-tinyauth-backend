<?php

namespace TinyAuthBackend\Model\Entity;

use Tools\Model\Entity\Entity;

/**
 * @property int $id
 * @property int $type
 * @property string $path
 * @property \Cake\I18n\FrozenTime|null $created
 * @property \Cake\I18n\FrozenTime|null $modified
 */
class AllowRule extends Entity {

    /**
     * @var int
     */
	public const TYPE_ALLOW = 1;

    /**
     * @var int
     */
	public const TYPE_DENY = 2;

	/**
	 * Fields that can be mass assigned using newEntity() or patchEntity().
	 *
	 * Note that when '*' is set to true, this allows all unspecified fields to
	 * be mass assigned. For security purposes, it is advised to set '*' to false
	 * (or remove it), and explicitly make individual fields accessible as needed.
	 *
	 * @var array<string, bool>
	 */
	protected $_accessible = [
		'type' => true,
		'path' => true,
		'created' => true,
		'modified' => true,
	];

	/**
	 * @param int|null $value
	 * @return array|string|null
	 */
	public static function types($value = null) {
		$options = [
			static::TYPE_ALLOW => __('allow'),
			static::TYPE_DENY => __('deny'),
		];

		return parent::enum($value, $options);
	}

}
