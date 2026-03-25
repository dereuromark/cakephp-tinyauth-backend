<?php
declare(strict_types=1);

namespace TinyAuthBackend\Model\Table;

use ArrayObject;
use Cake\Event\EventInterface;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * @method \TinyAuthBackend\Model\Entity\AllowRule get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \TinyAuthBackend\Model\Entity\AllowRule newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<\TinyAuthBackend\Model\Entity\AllowRule> newEntities(array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method \TinyAuthBackend\Model\Entity\AllowRule|false save(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method \TinyAuthBackend\Model\Entity\AllowRule patchEntity(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<\TinyAuthBackend\Model\Entity\AllowRule> patchEntities(iterable<\TinyAuthBackend\Model\Entity\AllowRule> $entities, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \TinyAuthBackend\Model\Entity\AllowRule findOrCreate($search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method \TinyAuthBackend\Model\Entity\AllowRule saveOrFail(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class AllowRulesTable extends Table {

	use ValidationTrait;

	/**
	 * @var array<string, string>
	 */
	public array $order = ['created' => 'DESC'];

	protected ?string $_table = 'tiny_auth_allow_rules';

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
			->add('path', 'unique', ['rule' => ['validateUnique', ['scope' => []]], 'provider' => 'table'])
			->add('path', 'valid', ['rule' => ['validatePath'], 'provider' => 'table']);

		$validator
			->integer('type')
			->requirePresence('type', 'create')
			->notEmptyString('type');

		return $validator;
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
