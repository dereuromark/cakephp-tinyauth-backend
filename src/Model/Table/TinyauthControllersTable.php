<?php
declare(strict_types=1);

namespace TinyAuthBackend\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * @method \TinyAuthBackend\Model\Entity\TinyauthController get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \TinyAuthBackend\Model\Entity\TinyauthController newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<\TinyAuthBackend\Model\Entity\TinyauthController> newEntities(array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \TinyAuthBackend\Model\Entity\TinyauthController|false save(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method \TinyAuthBackend\Model\Entity\TinyauthController patchEntity(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<\TinyAuthBackend\Model\Entity\TinyauthController> patchEntities(iterable<\TinyAuthBackend\Model\Entity\TinyauthController> $entities, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \TinyAuthBackend\Model\Entity\TinyauthController findOrCreate($search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \TinyAuthBackend\Model\Entity\TinyauthController saveOrFail(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method array<string, array<string, mixed>> findTree()
 * @property \TinyAuthBackend\Model\Table\ActionsTable $Actions
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class TinyauthControllersTable extends Table {

	/**
	 * @param array<string, mixed> $config The configuration for the Table.
	 *
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('tinyauth_controllers');
		$this->setDisplayField('name');
		$this->setPrimaryKey('id');

		$this->addBehavior('Timestamp');

		$this->hasMany('Actions', [
			'className' => 'TinyAuthBackend.Actions',
			'foreignKey' => 'controller_id',
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
			->scalar('plugin')
			->maxLength('plugin', 100)
			->allowEmptyString('plugin');

		$validator
			->scalar('prefix')
			->maxLength('prefix', 100)
			->allowEmptyString('prefix');

		$validator
			->scalar('name')
			->maxLength('name', 100)
			->requirePresence('name', 'create')
			->notEmptyString('name');

		return $validator;
	}

	/**
	 * @return array<string, array<string, mixed>>
	 */
	public function findTree(): array {
		/** @var array<\TinyAuthBackend\Model\Entity\TinyauthController> $controllers */
		$controllers = $this->find()
			->contain(['Actions'])
			->orderBy(['plugin' => 'ASC', 'prefix' => 'ASC', 'name' => 'ASC'])
			->all()
			->toArray();

		return $this->buildTree($controllers);
	}

	/**
	 * @param array<\TinyAuthBackend\Model\Entity\TinyauthController> $controllers
	 *
	 * @return array<string, array<string, mixed>>
	 */
	protected function buildTree(array $controllers): array {
		$tree = [];

		foreach ($controllers as $controller) {
			$plugin = $controller->plugin ?: 'App';
			$prefix = $controller->prefix ?: '_root';

			if (!isset($tree[$plugin])) {
				$tree[$plugin] = ['name' => $plugin, 'prefixes' => []];
			}
			if (!isset($tree[$plugin]['prefixes'][$prefix])) {
				$tree[$plugin]['prefixes'][$prefix] = ['name' => $prefix, 'controllers' => []];
			}
			$tree[$plugin]['prefixes'][$prefix]['controllers'][] = $controller;
		}

		return $tree;
	}

}
