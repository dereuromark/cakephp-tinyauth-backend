<?php
declare(strict_types=1);

namespace TinyAuthBackend\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * @method \TinyAuthBackend\Model\Entity\ResourceAbility get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \TinyAuthBackend\Model\Entity\ResourceAbility newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<\TinyAuthBackend\Model\Entity\ResourceAbility> newEntities(array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \TinyAuthBackend\Model\Entity\ResourceAbility|false save(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method \TinyAuthBackend\Model\Entity\ResourceAbility patchEntity(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<\TinyAuthBackend\Model\Entity\ResourceAbility> patchEntities(iterable<\TinyAuthBackend\Model\Entity\ResourceAbility> $entities, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \TinyAuthBackend\Model\Entity\ResourceAbility findOrCreate($search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \TinyAuthBackend\Model\Entity\ResourceAbility saveOrFail(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @property \TinyAuthBackend\Model\Table\ResourcesTable $Resources
 * @property \TinyAuthBackend\Model\Table\ResourceAclTable $ResourceAcl
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ResourceAbilitiesTable extends Table {

	/**
	 * @param array<string, mixed> $config The configuration for the Table.
	 *
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('tinyauth_resource_abilities');
		$this->setDisplayField('name');
		$this->setPrimaryKey('id');

		$this->addBehavior('Timestamp');

		$this->belongsTo('Resources', [
			'className' => 'TinyAuthBackend.Resources',
			'foreignKey' => 'resource_id',
		]);

		$this->hasMany('ResourceAcl', [
			'className' => 'TinyAuthBackend.ResourceAcl',
			'foreignKey' => 'resource_ability_id',
			'dependent' => true,
		]);
	}

	/**
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 *
	 * @return \Cake\Validation\Validator
	 */
	public function validationDefault(Validator $validator): Validator {
		$validator
			->integer('resource_id')
			->requirePresence('resource_id', 'create')
			->notEmptyString('resource_id');

		$validator
			->scalar('name')
			->maxLength('name', 50)
			->requirePresence('name', 'create')
			->notEmptyString('name')
			->add('name', 'uniquePerResource', [
				'rule' => function ($value, $context) {
					if (empty($context['data']['resource_id'])) {
						return true;
					}
					$conditions = [
						'resource_id' => $context['data']['resource_id'],
						'name' => $value,
					];
					// Exclude current record when editing
					if (!empty($context['data']['id'])) {
						$conditions['id !='] = $context['data']['id'];
					}

					return !$this->exists($conditions);
				},
				'message' => __('This ability name already exists for this resource.'),
			]);

		$validator
			->scalar('description')
			->maxLength('description', 200)
			->allowEmptyString('description');

		return $validator;
	}

}
