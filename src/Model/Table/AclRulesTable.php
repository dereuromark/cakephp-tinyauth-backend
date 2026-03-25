<?php
declare(strict_types=1);

namespace TinyAuthBackend\Model\Table;

use ArrayObject;
use Cake\Event\EventInterface;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * @method \TinyAuthBackend\Model\Entity\AclRule get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \TinyAuthBackend\Model\Entity\AclRule newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<\TinyAuthBackend\Model\Entity\AclRule> newEntities(array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \TinyAuthBackend\Model\Entity\AclRule|false save(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method \TinyAuthBackend\Model\Entity\AclRule patchEntity(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<\TinyAuthBackend\Model\Entity\AclRule> patchEntities(iterable<\TinyAuthBackend\Model\Entity\AclRule> $entities, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \TinyAuthBackend\Model\Entity\AclRule findOrCreate($search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \TinyAuthBackend\Model\Entity\AclRule saveOrFail(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class AclRulesTable extends Table {

	use ValidationTrait;

	/**
	 * @var array<string, string>
	 */
	public array $order = ['created' => 'DESC'];

	protected ?string $_table = 'tiny_auth_acl_rules';

	/**
	 * Initialize method
	 *
	 * @param array<string, mixed> $config The configuration for the Table.
	 *
	 * @return void
	 */
	public function initialize(array $config): void {
		parent::initialize($config);

		$this->addBehavior('Timestamp');
	}

	/**
	 * Default validation rules.
	 *
	 * @param \Cake\Validation\Validator $validator Validator instance.
	 *
	 * @return \Cake\Validation\Validator
	 */
	public function validationDefault(Validator $validator): Validator {
		$validator
			->requirePresence('path', 'create')
			->notEmptyString('path')
			->add('path', 'unique', ['rule' => ['validateUnique', ['scope' => ['role']]], 'provider' => 'table'])
			->add('path', 'valid', ['rule' => ['validatePath'], 'provider' => 'table']);

		$validator
			->integer('type')
			->requirePresence('type', 'create')
			->notEmptyString('type');

		$validator
			->requirePresence('role', 'create')
			->notEmptyString('role');

		return $validator;
	}

	/**
	 * @param \Cake\Event\EventInterface<\Cake\ORM\Table> $event
	 * @param \ArrayObject<string, mixed> $data
	 * @param \ArrayObject<string, mixed> $options
	 * @return void
	 */
	public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options): void {
		if (empty($data['path'])) {
			return;
		}

		$path = $this->normalizePath($data['path']);
		if ($path === $data['path']) {
			return;
		}

		$data['path'] = $path;
	}

	/**
	 * @param string $path
	 *
	 * @return bool
	 */
	public function validatePath($path) {
		return $this->assertValidPath($path);
	}

}
