<?php
declare(strict_types=1);

namespace TinyAuthBackend\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * @method \TinyAuthBackend\Model\Entity\Role get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \TinyAuthBackend\Model\Entity\Role newEntity(array $data, array $options = [])
 * @method array<\TinyAuthBackend\Model\Entity\Role> newEntities(array $data, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\Role|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\Role patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\TinyAuthBackend\Model\Entity\Role> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\Role findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\Role saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @property \TinyAuthBackend\Model\Table\RolesTable&\Cake\ORM\Association\BelongsTo $ParentRoles
 * @property \TinyAuthBackend\Model\Table\RolesTable&\Cake\ORM\Association\HasMany $ChildRoles
 * @property \TinyAuthBackend\Model\Table\AclPermissionsTable&\Cake\ORM\Association\HasMany $AclPermissions
 * @property \TinyAuthBackend\Model\Table\ResourceAclTable&\Cake\ORM\Association\HasMany $ResourceAcl
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class RolesTable extends Table {

	/**
	 * @param array $config The configuration for the Table.
	 *
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->setTable('tinyauth_roles');
		$this->setDisplayField('name');
		$this->setPrimaryKey('id');

		$this->addBehavior('Timestamp');

		$this->belongsTo('ParentRoles', [
			'className' => 'TinyAuthBackend.Roles',
			'foreignKey' => 'parent_id',
		]);

		$this->hasMany('ChildRoles', [
			'className' => 'TinyAuthBackend.Roles',
			'foreignKey' => 'parent_id',
		]);

		$this->hasMany('AclPermissions', [
			'className' => 'TinyAuthBackend.AclPermissions',
			'foreignKey' => 'role_id',
		]);

		$this->hasMany('ResourceAcl', [
			'className' => 'TinyAuthBackend.ResourceAcl',
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
			->scalar('name')
			->maxLength('name', 100)
			->requirePresence('name', 'create')
			->notEmptyString('name');

		$validator
			->scalar('alias')
			->maxLength('alias', 50)
			->requirePresence('alias', 'create')
			->notEmptyString('alias')
			->add('alias', 'unique', [
				'rule' => 'validateUnique',
				'provider' => 'table',
				'message' => __('This alias is already in use.'),
			]);

		$validator
			->integer('parent_id')
			->allowEmptyString('parent_id');

		$validator
			->integer('sort_order')
			->notEmpty('sort_order');

		return $validator;
	}

	/**
	 * @return array<\TinyAuthBackend\Model\Entity\Role>
	 */
	public function findHierarchy(): array {
		$roles = $this->find()
			->orderBy(['parent_id' => 'ASC', 'sort_order' => 'ASC'])
			->all()
			->toArray();

		return $this->buildTree($roles);
	}

	/**
	 * @param array<\TinyAuthBackend\Model\Entity\Role> $roles
	 * @param int|null $parentId
	 *
	 * @return array<\TinyAuthBackend\Model\Entity\Role>
	 */
	protected function buildTree(array $roles, ?int $parentId = null): array {
		$tree = [];
		foreach ($roles as $role) {
			if ($role->parent_id === $parentId) {
				$role->children = $this->buildTree($roles, $role->id);
				$tree[] = $role;
			}
		}

		return $tree;
	}

}
