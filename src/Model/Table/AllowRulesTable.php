<?php
namespace TinyAuthBackend\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * @method \TinyAuthBackend\Model\Entity\AllowRule get($primaryKey, $options = [])
 * @method \TinyAuthBackend\Model\Entity\AllowRule newEntity($data = null, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\AllowRule[] newEntities(array $data, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\AllowRule|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \TinyAuthBackend\Model\Entity\AllowRule patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\AllowRule[] patchEntities($entities, array $data, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\AllowRule findOrCreate($search, callable $callback = null, $options = [])
 * @method \TinyAuthBackend\Model\Entity\AllowRule saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class AllowRulesTable extends Table {

	/**
	 * @var array
	 */
	public $order = ['created' => 'DESC'];

	/**
	 * @var string
	 */
	protected $_table = 'tiny_auth_allow_rules';

	/**
	 * Initialize method
	 *
	 * @param array $config The configuration for the Table.
	 *
	 * @return void
	 */
	public function initialize(array $config) {
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
	public function validationDefault(Validator $validator) {
		$validator
			->requirePresence('path', 'create')
			->notEmptyString('path')
			->add('path', 'unique', ['rule' => ['validateUnique', ['scope' => []]], 'provider' => 'table']);

		$validator
			->integer('type')
			->requirePresence('type', 'create')
			->notEmptyString('type');

		return $validator;
	}

}
