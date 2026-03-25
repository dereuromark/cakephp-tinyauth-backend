<?php
declare(strict_types=1);

namespace TinyAuthBackend\Model\Table;

use ArrayObject;
use Cake\Cache\Cache;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * @method \TinyAuthBackend\Model\Entity\ResourceAcl get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \TinyAuthBackend\Model\Entity\ResourceAcl newEntity(array $data, array $options = [])
 * @method array<\TinyAuthBackend\Model\Entity\ResourceAcl> newEntities(array $data, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\ResourceAcl|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\ResourceAcl patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\TinyAuthBackend\Model\Entity\ResourceAcl> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\ResourceAcl findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\ResourceAcl saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @property \TinyAuthBackend\Model\Table\ResourceAbilitiesTable&\Cake\ORM\Association\BelongsTo $ResourceAbilities
 * @property \TinyAuthBackend\Model\Table\RolesTable&\Cake\ORM\Association\BelongsTo $Roles
 * @property \TinyAuthBackend\Model\Table\ScopesTable&\Cake\ORM\Association\BelongsTo $Scopes
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ResourceAclTable extends Table {

	/**
	 * @param array $config The configuration for the Table.
	 *
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('tinyauth_resource_acl');
		$this->setDisplayField('id');
		$this->setPrimaryKey('id');

		$this->addBehavior('Timestamp');

		$this->belongsTo('ResourceAbilities', [
			'className' => 'TinyAuthBackend.ResourceAbilities',
			'foreignKey' => 'resource_ability_id',
		]);

		$this->belongsTo('Roles', [
			'className' => 'TinyAuthBackend.Roles',
			'foreignKey' => 'role_id',
		]);

		$this->belongsTo('Scopes', [
			'className' => 'TinyAuthBackend.Scopes',
			'foreignKey' => 'scope_id',
		]);
	}

	/**
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 *
	 * @return \Cake\Validation\Validator
	 */
	public function validationDefault(Validator $validator): Validator {
		$validator
			->integer('resource_ability_id')
			->requirePresence('resource_ability_id', 'create')
			->notEmptyString('resource_ability_id');

		$validator
			->integer('role_id')
			->requirePresence('role_id', 'create')
			->notEmptyString('role_id');

		$validator
			->scalar('type')
			->inList('type', ['allow', 'deny'])
			->notEmptyString('type');

		$validator
			->integer('scope_id')
			->allowEmptyString('scope_id');

		return $validator;
	}

	/**
	 * @param \Cake\Event\EventInterface $event
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @param \ArrayObject $options
	 *
	 * @return void
	 */
	public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void {
		Cache::delete('TinyAuth.resources');
	}

	/**
	 * @param \Cake\Event\EventInterface $event
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @param \ArrayObject $options
	 *
	 * @return void
	 */
	public function afterDelete(EventInterface $event, EntityInterface $entity, ArrayObject $options): void {
		Cache::delete('TinyAuth.resources');
	}

}
