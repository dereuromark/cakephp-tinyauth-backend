<?php

namespace TinyAuthBackend\Controller\Admin;

use App\Controller\AppController;
use Cake\Event\EventInterface;
use TinyAuth\Utility\TinyAuth;
use TinyAuthBackend\Utility\AdapterConfig;

/**
 * @property \TinyAuthBackend\Model\Table\AllowRulesTable $AllowRules
 * @method \Cake\Datasource\ResultSetInterface<\TinyAuthBackend\Model\Entity\AllowRule> paginate($object = null, array $settings = [])
 */
class AllowController extends AppController {

	/**
	 * @var string
	 */
	protected $modelClass = 'TinyAuthBackend.AllowRules';

	/**
	 * @param \Cake\Event\EventInterface $event
	 *
	 * @return \Cake\Http\Response|null|void
	 */
	public function beforeFilter(EventInterface $event) {
		if (!AdapterConfig::isAllowEnabled()) {
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
		$allowRules = $this->paginate($this->AllowRules);

		$this->set(compact('allowRules'));
	}

	/**
	 * View method
	 *
	 * @param string|null $id Tiny Auth Allow Rule id.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 * @return \Cake\Http\Response|null|void
	 */
	public function view($id = null) {
		$allowRule = $this->AllowRules->get($id, [
			'contain' => [],
		]);

		$this->set('allowRule', $allowRule);
	}

	/**
	 * Add method
	 *
	 * @return \Cake\Http\Response|null|void
	 */
	public function add() {
		$allowRule = $this->AllowRules->newEmptyEntity();
		if ($this->request->is('post')) {
			$allowRule = $this->AllowRules->patchEntity($allowRule, (array)$this->request->getData());
			if ($this->AllowRules->save($allowRule)) {
				$this->Flash->success(__('The tiny auth allow rule has been saved.'));

				return $this->redirect(['action' => 'index']);
			}
			$this->Flash->error(__('The tiny auth allow rule could not be saved. Please, try again.'));
		}
		$this->set(compact('allowRule'));
	}

	/**
	 * Edit method
	 *
	 * @param string|null $id Tiny Auth Allow Rule id.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 * @return \Cake\Http\Response|null|void
	 */
	public function edit($id = null) {
		$allowRule = $this->AllowRules->get($id, [
			'contain' => [],
		]);
		if ($this->request->is(['patch', 'post', 'put'])) {
			$allowRule = $this->AllowRules->patchEntity($allowRule, (array)$this->request->getData());
			if ($this->AllowRules->save($allowRule)) {
				$this->Flash->success(__('The tiny auth allow rule has been saved.'));

				return $this->redirect(['action' => 'index']);
			}
			$this->Flash->error(__('The tiny auth allow rule could not be saved. Please, try again.'));
		}
		$this->set(compact('allowRule'));
	}

	/**
	 * Delete method
	 *
	 * @param string|null $id Tiny Auth Allow Rule id.
	 * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
	 * @return \Cake\Http\Response|null Redirects to index.
	 */
	public function delete($id = null) {
		$this->request->allowMethod(['post', 'delete']);
		$allowRule = $this->AllowRules->get($id);
		if ($this->AllowRules->delete($allowRule)) {
			$this->Flash->success(__('The tiny auth allow rule has been deleted.'));
		} else {
			$this->Flash->error(__('The tiny auth allow rule could not be deleted. Please, try again.'));
		}

		return $this->redirect(['action' => 'index']);
	}

}
