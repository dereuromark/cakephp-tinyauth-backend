<?php
declare(strict_types=1);

namespace TinyAuthBackend\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * @method \TinyAuthBackend\Model\Entity\Scope get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \TinyAuthBackend\Model\Entity\Scope newEntity(array $data, array $options = [])
 * @method array<\TinyAuthBackend\Model\Entity\Scope> newEntities(array $data, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\Scope|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\Scope patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\TinyAuthBackend\Model\Entity\Scope> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\Scope findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\Scope saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ScopesTable extends Table {

	/**
	 * @param array $config The configuration for the Table.
	 *
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('tinyauth_scopes');
		$this->setDisplayField('name');
		$this->setPrimaryKey('id');

		$this->addBehavior('Timestamp');
	}

	/**
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 *
	 * @return \Cake\Validation\Validator
	 */
	public function validationDefault(Validator $validator): Validator {
		$validator
			->scalar('name')
			->maxLength('name', 50)
			->requirePresence('name', 'create')
			->notEmptyString('name')
			->add('name', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

		$validator
			->scalar('description')
			->maxLength('description', 200)
			->allowEmptyString('description');

		$validator
			->scalar('entity_field')
			->maxLength('entity_field', 100)
			->requirePresence('entity_field', 'create')
			->notEmptyString('entity_field');

		$validator
			->scalar('user_field')
			->maxLength('user_field', 100)
			->requirePresence('user_field', 'create')
			->notEmptyString('user_field');

		return $validator;
	}

}
