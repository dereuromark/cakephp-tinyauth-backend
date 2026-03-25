<?php
declare(strict_types=1);

namespace TinyAuthBackend\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * @method \TinyAuthBackend\Model\Entity\ResourceAbility get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \TinyAuthBackend\Model\Entity\ResourceAbility newEntity(array $data, array $options = [])
 * @method array<\TinyAuthBackend\Model\Entity\ResourceAbility> newEntities(array $data, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\ResourceAbility|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\ResourceAbility patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\TinyAuthBackend\Model\Entity\ResourceAbility> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\ResourceAbility findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\ResourceAbility saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @property \TinyAuthBackend\Model\Table\ResourcesTable&\Cake\ORM\Association\BelongsTo $Resources
 * @property \TinyAuthBackend\Model\Table\ResourceAclTable&\Cake\ORM\Association\HasMany $ResourceAcl
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ResourceAbilitiesTable extends Table {

	/**
	 * @param array $config The configuration for the Table.
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
			->notEmptyString('name');

		$validator
			->scalar('description')
			->maxLength('description', 200)
			->allowEmptyString('description');

		return $validator;
	}

}
