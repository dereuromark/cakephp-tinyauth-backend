<?php
namespace TinyAuthBackend\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * @method \TinyAuthBackend\Model\Entity\AclRule get($primaryKey, $options = [])
 * @method \TinyAuthBackend\Model\Entity\AclRule newEntity($data = null, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\AclRule[] newEntities(array $data, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\AclRule|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \TinyAuthBackend\Model\Entity\AclRule patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\AclRule[] patchEntities($entities, array $data, array $options = [])
 * @method \TinyAuthBackend\Model\Entity\AclRule findOrCreate($search, callable $callback = null, $options = [])
 * @method \TinyAuthBackend\Model\Entity\AclRule saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 */
class AclRulesTable extends Table {

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
			->notEmpty('path')
			->add('path', 'unique', ['rule' => ['validateUnique', ['scope' => ['role']]], 'provider' => 'table']);

		return $validator;
	}

}
