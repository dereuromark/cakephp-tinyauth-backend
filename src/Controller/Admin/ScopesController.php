<?php
declare(strict_types=1);

namespace TinyAuthBackend\Controller\Admin;

use Cake\Http\Response;

class ScopesController extends AppController {

	/**
	 * @return void
	 */
	public function index(): void {
		$scopesTable = $this->fetchTable('TinyAuthBackend.Scopes');
		$scopes = $scopesTable->find()
			->orderBy(['name' => 'ASC'])
			->all()
			->toArray();

		$this->set(compact('scopes'));
	}

	/**
	 * @return \Cake\Http\Response|null
	 */
	public function add(): ?Response {
		$scopesTable = $this->fetchTable('TinyAuthBackend.Scopes');
		$scope = $scopesTable->newEmptyEntity();

		if ($this->request->is('post')) {
			$scope = $scopesTable->patchEntity($scope, $this->request->getData());
			if ($scopesTable->save($scope)) {
				$this->Flash->success(__('Scope saved.'));

				return $this->redirect(['action' => 'index']);
			}
			$this->Flash->error(__('Could not save scope.'));
		}

		$this->set(compact('scope'));

		return $this->render('form');
	}

	/**
	 * @param int $id Scope ID.
	 * @return \Cake\Http\Response|null
	 */
	public function edit(int $id): ?Response {
		$scopesTable = $this->fetchTable('TinyAuthBackend.Scopes');
		$scope = $scopesTable->get($id);

		if ($this->request->is(['post', 'put', 'patch'])) {
			$scope = $scopesTable->patchEntity($scope, $this->request->getData());
			if ($scopesTable->save($scope)) {
				$this->Flash->success(__('Scope updated.'));

				return $this->redirect(['action' => 'index']);
			}
			$this->Flash->error(__('Could not update scope.'));
		}

		$this->set(compact('scope'));

		return $this->render('form');
	}

	/**
	 * @param int $id Scope ID.
	 * @return \Cake\Http\Response|null
	 */
	public function delete(int $id): ?Response {
		$this->request->allowMethod(['post', 'delete']);

		$scopesTable = $this->fetchTable('TinyAuthBackend.Scopes');
		$scope = $scopesTable->get($id);

		// Check if scope is in use
		$resourceAclTable = $this->fetchTable('TinyAuthBackend.ResourceAcl');
		$usageCount = $resourceAclTable->find()->where(['scope_id' => $id])->count();
		if ($usageCount > 0) {
			$this->Flash->error(__('Cannot delete scope. It is used by {0} resource permission(s).', $usageCount));

			return $this->redirect(['action' => 'index']);
		}

		if ($scopesTable->delete($scope)) {
			$this->Flash->success(__('Scope deleted.'));
		} else {
			$this->Flash->error(__('Could not delete scope.'));
		}

		return $this->redirect(['action' => 'index']);
	}

}
