<?php
declare(strict_types=1);

namespace TinyAuthBackend\Model\Table;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use TinyAuthBackend\Utility\CacheInvalidator;

/**
 * @method \TinyAuthBackend\Model\Entity\Action get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \TinyAuthBackend\Model\Entity\Action newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<\TinyAuthBackend\Model\Entity\Action> newEntities(array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \TinyAuthBackend\Model\Entity\Action|false save(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method \TinyAuthBackend\Model\Entity\Action patchEntity(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<\TinyAuthBackend\Model\Entity\Action> patchEntities(iterable<\TinyAuthBackend\Model\Entity\Action> $entities, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \TinyAuthBackend\Model\Entity\Action findOrCreate($search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \TinyAuthBackend\Model\Entity\Action saveOrFail(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @property \TinyAuthBackend\Model\Table\TinyauthControllersTable $TinyauthControllers
 * @property \TinyAuthBackend\Model\Table\AclPermissionsTable $AclPermissions
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ActionsTable extends Table {

	/**
	 * @param array<string, mixed> $config The configuration for the Table.
	 *
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('tinyauth_actions');
		$this->setDisplayField('name');
		$this->setPrimaryKey('id');

		$this->addBehavior('Timestamp');

		$this->belongsTo('TinyauthControllers', [
			'className' => 'TinyAuthBackend.TinyauthControllers',
			'foreignKey' => 'controller_id',
		]);

		$this->hasMany('AclPermissions', [
			'className' => 'TinyAuthBackend.AclPermissions',
			'foreignKey' => 'action_id',
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
			->integer('controller_id')
			->requirePresence('controller_id', 'create')
			->notEmptyString('controller_id');

		$validator
			->scalar('name')
			->maxLength('name', 100)
			->requirePresence('name', 'create')
			->notEmptyString('name');

		$validator
			->boolean('is_public');

		return $validator;
	}

	/**
	 * @param \Cake\Event\EventInterface<\Cake\ORM\Table> $event
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @param \ArrayObject<string, mixed> $options
	 *
	 * @return void
	 */
	public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void {
		if ($entity->isDirty('is_public')) {
			CacheInvalidator::clearAllow();
		}
	}

	/**
	 * @param \Cake\Event\EventInterface<\Cake\ORM\Table> $event
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @param \ArrayObject<string, mixed> $options
	 *
	 * @return void
	 */
	public function afterDelete(EventInterface $event, EntityInterface $entity, ArrayObject $options): void {
		CacheInvalidator::clearAll();
	}

}
