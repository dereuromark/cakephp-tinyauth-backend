<?php
declare(strict_types=1);

namespace TinyAuthBackend\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * @method \TinyAuthBackend\Model\Entity\Resource get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \TinyAuthBackend\Model\Entity\Resource newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<\TinyAuthBackend\Model\Entity\Resource> newEntities(array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \TinyAuthBackend\Model\Entity\Resource|false save(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method \TinyAuthBackend\Model\Entity\Resource patchEntity(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<\TinyAuthBackend\Model\Entity\Resource> patchEntities(iterable<\TinyAuthBackend\Model\Entity\Resource> $entities, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \TinyAuthBackend\Model\Entity\Resource findOrCreate($search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \TinyAuthBackend\Model\Entity\Resource saveOrFail(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @property \TinyAuthBackend\Model\Table\ResourceAbilitiesTable $ResourceAbilities
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ResourcesTable extends Table {

	/**
	 * @param array<string, mixed> $config The configuration for the Table.
	 *
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('tinyauth_resources');
		$this->setDisplayField('name');
		$this->setPrimaryKey('id');

		$this->addBehavior('Timestamp');

		$this->hasMany('ResourceAbilities', [
			'className' => 'TinyAuthBackend.ResourceAbilities',
			'foreignKey' => 'resource_id',
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
			->scalar('name')
			->maxLength('name', 100)
			->requirePresence('name', 'create')
			->notEmptyString('name')
			->add('name', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

		$validator
			->scalar('entity_class')
			->maxLength('entity_class', 200)
			->requirePresence('entity_class', 'create')
			->notEmptyString('entity_class');

		$validator
			->scalar('table_name')
			->maxLength('table_name', 100)
			->requirePresence('table_name', 'create')
			->notEmptyString('table_name');

		return $validator;
	}

}
