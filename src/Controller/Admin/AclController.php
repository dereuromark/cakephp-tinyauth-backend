<?php

namespace TinyAuthBackend\Controller\Admin;

use App\Controller\AppController;
use Cake\Event\EventInterface;
use TinyAuth\Utility\TinyAuth;
use TinyAuthBackend\Utility\AdapterConfig;

/**
 * @property \TinyAuthBackend\Model\Table\AclRulesTable $AclRules
 * @method \TinyAuthBackend\Model\Entity\AclRule[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class AclController extends AppController {

	/**
	 * @var string
	 */
	protected $modelClass = 'TinyAuthBackend.AclRules';

	/**
	 * @param \Cake\Event\EventInterface $event
	 *
	 * @return \Cake\Http\Response|null|void
	 */
	public function beforeFilter(EventInterface $event) {
		if (!AdapterConfig::isAclEnabled()) {
			$this->Flash->error('Not enabled');

			return $this->redirect(['controller' => 'Auth']);
		}
	}

	/**
	 * @param \Cake\Event\EventInterface $event
	 *
	 * @return \Cake\Http\Response|null|void
	 */
	public function beforeRender(EventInterface $event) {
		$availableRoles = (new TinyAuth())->getAvailableRoles();
		$roles = (array)array_combine(array_keys($availableRoles), array_keys($availableRoles));
		$roles['*'] = '*';

		$this->set(compact('roles'));
	}

	/**
	 * Index method
	 *
	 * @return \Cake\Http\Response|null|void
	 */
	public function index() {
		$aclRules = $this->paginate($this->AclRules);

		$this->set(compact('aclRules'));
	}

	/**
	 * View method
	 *
	 * @param string|null $id Tiny Auth Acl Rule id.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 * @return \Cake\Http\Response|null|void
	 */
	public function view($id = null) {
		$aclRule = $this->AclRules->get($id, [
			'contain' => [],
		]);

		$this->set('aclRule', $aclRule);
	}

	/**
	 * Add method
	 *
	 * @return \Cake\Http\Response|null|void
	 */
	public function add() {
		$aclRule = $this->AclRules->newEmptyEntity();
		if ($this->request->is('post')) {
			$aclRule = $this->AclRules->patchEntity($aclRule, (array)$this->request->getData());
			if ($this->AclRules->save($aclRule)) {
				$this->Flash->success(__('The tiny auth acl rule has been saved.'));

				return $this->redirect(['action' => 'index']);
			}
			$this->Flash->error(__('The tiny auth acl rule could not be saved. Please, try again.'));
		}
		$this->set(compact('aclRule'));
	}

	/**
	 * Edit method
	 *
	 * @param string|null $id Tiny Auth Acl Rule id.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 * @return \Cake\Http\Response|null|void
	 */
	public function edit($id = null) {
		$aclRule = $this->AclRules->get($id, [
			'contain' => [],
		]);
		if ($this->request->is(['patch', 'post', 'put'])) {
			$aclRule = $this->AclRules->patchEntity($aclRule, (array)$this->request->getData());
			if ($this->AclRules->save($aclRule)) {
				$this->Flash->success(__('The tiny auth acl rule has been saved.'));

				return $this->redirect(['action' => 'index']);
			}
			$this->Flash->error(__('The tiny auth acl rule could not be saved. Please, try again.'));
		}
		$this->set(compact('aclRule'));
	}

	/**
	 * Delete method
	 *
	 * @param string|null $id Tiny Auth Acl Rule id.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 * @return \Cake\Http\Response|null Redirects to index.
	 */
	public function delete($id = null) {
		$this->request->allowMethod(['post', 'delete']);
		$aclRule = $this->AclRules->get($id);
		if ($this->AclRules->delete($aclRule)) {
			$this->Flash->success(__('The tiny auth acl rule has been deleted.'));
		} else {
			$this->Flash->error(__('The tiny auth acl rule could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

}
