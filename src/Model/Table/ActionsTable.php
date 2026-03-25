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
 * @method \TinyAuthBackend\Model\Entity\Action get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \TinyAuthBackend\Model\Entity\Action newEntity(array $data, array $options = [])
 * @method array<\TinyAuthBackend\Model\Entity\Action> newEntities(array $data, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\Action|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\Action patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\TinyAuthBackend\Model\Entity\Action> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\Action findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\Action saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @property \TinyAuthBackend\Model\Table\TinyauthControllersTable&\Cake\ORM\Association\BelongsTo $TinyauthControllers
 * @property \TinyAuthBackend\Model\Table\AclPermissionsTable&\Cake\ORM\Association\HasMany $AclPermissions
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ActionsTable extends Table {

	/**
	 * @param array $config The configuration for the Table.
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
			->notEmpty('controller_id');

		$validator
			->scalar('name')
			->maxLength('name', 100)
			->requirePresence('name', 'create')
			->notEmptyString('name');

		$validator
			->boolean('is_public')
			->notEmpty('is_public');

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
		if ($entity->isDirty('is_public')) {
			Cache::delete('TinyAuth.allow');
		}
	}

	/**
	 * @param \Cake\Event\EventInterface $event
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @param \ArrayObject $options
	 *
	 * @return void
	 */
	public function afterDelete(EventInterface $event, EntityInterface $entity, ArrayObject $options): void {
		Cache::delete('TinyAuth.allow');
	}

}
