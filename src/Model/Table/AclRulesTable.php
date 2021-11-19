<?php

namespace TinyAuthBackend\Model\Table;

use ArrayObject;
use Cake\Event\EventInterface;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * @method \TinyAuthBackend\Model\Entity\AclRule get($primaryKey, $options = [])
 * @method \TinyAuthBackend\Model\Entity\AclRule newEntity($data = null, array $options = [])
 * @method array<\TinyAuthBackend\Model\Entity\AclRule> newEntities(array $data, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\AclRule|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \TinyAuthBackend\Model\Entity\AclRule patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\TinyAuthBackend\Model\Entity\AclRule> patchEntities($entities, array $data, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\AclRule findOrCreate($search, callable $callback = null, $options = [])
 * @method \TinyAuthBackend\Model\Entity\AclRule saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class AclRulesTable extends Table {

	use ValidationTrait;

	/**
	 * @var array
	 */
	public $order = ['created' => 'DESC'];

	/**
	 * @var string
	 */
	protected $_table = 'tiny_auth_acl_rules';

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
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
	 * @param \Cake\Event\EventInterface $event
	 * @param \ArrayObject $data
	 * @param \ArrayObject $options
	 * @return void
	 */
	public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options) {
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
