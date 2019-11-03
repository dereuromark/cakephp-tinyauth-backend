<?php

namespace TinyAuthBackend\Controller\Admin;

use App\Controller\AppController;
use Cake\Event\Event;
use TinyAuthBackend\Utility\Config;

/**
 * @property \TinyAuthBackend\Model\Table\AclRulesTable $AclRules
 * @method \TinyAuthBackend\Model\Entity\AclRule[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class AclController extends AppController {

	/**
	 * @var string
	 */
	public $modelClass = 'TinyAuthBackend.AclRules';

	/**
	 * @param \Cake\Event\Event $event
	 *
	 * @return \Cake\Http\Response|null|void
	 */
	public function beforeFilter(Event $event) {
		if (!Config::isAclEnabled()) {
			$this->Flash->error('Not enabled');
			return $this->redirect(['controller' => 'Auth']);
		}
	}

	/**
	 * Index method
	 *
	 * @return \Cake\Http\Response|null
	 */
	public function index() {
		$aclRules = $this->paginate($this->AclRules);

		$this->set(compact('aclRules'));
	}

	/**
	 * View method
	 *
	 * @param string|null $id Tiny Auth Acl Rule id.
	 * @return \Cake\Http\Response|null
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function view($id = null) {
		$aclRule = $this->AclRules->get($id, [
			'contain' => []
		]);

		$this->set('aclRule', $aclRule);
	}

	/**
	 * Add method
	 *
	 * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
	 */
	public function add() {
		$aclRule = $this->AclRules->newEntity();
		if ($this->request->is('post')) {
			$aclRule = $this->AclRules->patchEntity($aclRule, $this->request->getData());
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
	 * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 */
	public function edit($id = null) {
		$aclRule = $this->AclRules->get($id, [
			'contain' => []
		]);
		if ($this->request->is(['patch', 'post', 'put'])) {
			$aclRule = $this->AclRules->patchEntity($aclRule, $this->request->getData());
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
	 * @return \Cake\Http\Response|null Redirects to index.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
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
