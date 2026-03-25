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
 * @method \TinyAuthBackend\Model\Entity\AclPermission get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \TinyAuthBackend\Model\Entity\AclPermission newEntity(array $data, array $options = [])
 * @method array<\TinyAuthBackend\Model\Entity\AclPermission> newEntities(array $data, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\AclPermission|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\AclPermission patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\TinyAuthBackend\Model\Entity\AclPermission> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\AclPermission findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\AclPermission saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @property \TinyAuthBackend\Model\Table\ActionsTable&\Cake\ORM\Association\BelongsTo $Actions
 * @property \TinyAuthBackend\Model\Table\RolesTable&\Cake\ORM\Association\BelongsTo $Roles
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class AclPermissionsTable extends Table {

	/**
	 * @param array $config The configuration for the Table.
	 *
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('tinyauth_acl_permissions');
		$this->setDisplayField('id');
		$this->setPrimaryKey('id');

		$this->addBehavior('Timestamp');

		$this->belongsTo('Actions', [
			'className' => 'TinyAuthBackend.Actions',
			'foreignKey' => 'action_id',
		]);

		$this->belongsTo('Roles', [
			'className' => 'TinyAuthBackend.Roles',
			'foreignKey' => 'role_id',
		]);
	}

	/**
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 *
	 * @return \Cake\Validation\Validator
	 */
	public function validationDefault(Validator $validator): Validator {
		$validator
			->integer('action_id')
			->requirePresence('action_id', 'create')
			->notEmptyString('action_id');

		$validator
			->integer('role_id')
			->requirePresence('role_id', 'create')
			->notEmptyString('role_id');

		$validator
			->scalar('type')
			->inList('type', ['allow', 'deny'])
			->notEmptyString('type');

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
		Cache::delete('TinyAuth.acl');
	}

	/**
	 * @param \Cake\Event\EventInterface $event
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @param \ArrayObject $options
	 *
	 * @return void
	 */
	public function afterDelete(EventInterface $event, EntityInterface $entity, ArrayObject $options): void {
		Cache::delete('TinyAuth.acl');
	}

}
