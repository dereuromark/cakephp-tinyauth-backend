<?php
declare(strict_types=1);

namespace TinyAuthBackend\Controller\Admin;

use Cake\Controller\Controller;
use Cake\Http\Response;
use TinyAuthBackend\Service\RoleSourceService;

class RolesController extends Controller {

	protected RoleSourceService $roleSource;

	/**
	 * @return void
	 */
	public function initialize(): void {
		parent::initialize();
		$this->viewBuilder()->setLayout('TinyAuthBackend.tinyauth');
		$this->roleSource = new RoleSourceService();
	}

	/**
	 * @return void
	 */
	public function index(): void {
		$isManaged = $this->roleSource->isManaged();
		$roles = $this->roleSource->getRoleEntities();

		// Build hierarchy only if managed (has parent_id)
		$hierarchy = [];
		if ($isManaged) {
			$rolesTable = $this->fetchTable('TinyAuthBackend.Roles');
			$hierarchy = $rolesTable->findHierarchy();
		}

		$this->set(compact('isManaged', 'hierarchy', 'roles'));
	}

	/**
	 * @return \Cake\Http\Response|null
	 */
	public function add(): ?Response {
		if (!$this->roleSource->isManaged()) {
			$this->Flash->error(__('Roles are managed externally and cannot be created here.'));

			return $this->redirect(['action' => 'index']);
		}

		$rolesTable = $this->fetchTable('TinyAuthBackend.Roles');
		$role = $rolesTable->newEmptyEntity();

		if ($this->request->is('post')) {
			$role = $rolesTable->patchEntity($role, $this->request->getData());
			if ($rolesTable->save($role)) {
				$this->roleSource->clearCache();
				$this->Flash->success(__('Role saved.'));

				return $this->redirect(['action' => 'index']);
			}
			$this->Flash->error(__('Could not save role.'));
		}

		$parents = $rolesTable->find('list', keyField: 'id', valueField: 'name')
			->orderBy(['name' => 'ASC'])
			->toArray();

		$this->set(compact('role', 'parents'));

		return $this->render('form');
	}

	/**
	 * @param int $id Role ID.
	 * @return \Cake\Http\Response|null
	 */
	public function edit(int $id): ?Response {
		if (!$this->roleSource->isManaged()) {
			$this->Flash->error(__('Roles are managed externally and cannot be edited here.'));

			return $this->redirect(['action' => 'index']);
		}

		$rolesTable = $this->fetchTable('TinyAuthBackend.Roles');
		$role = $rolesTable->get($id);

		if ($this->request->is(['post', 'put', 'patch'])) {
			$role = $rolesTable->patchEntity($role, $this->request->getData());
			if ($rolesTable->save($role)) {
				$this->roleSource->clearCache();
				$this->Flash->success(__('Role updated.'));

				return $this->redirect(['action' => 'index']);
			}
			$this->Flash->error(__('Could not update role.'));
		}

		// Exclude self from parent options to prevent self-reference
		$parents = $rolesTable->find('list', keyField: 'id', valueField: 'name')
			->where(['id !=' => $id])
			->orderBy(['name' => 'ASC'])
			->toArray();

		$this->set(compact('role', 'parents'));

		return $this->render('form');
	}

	/**
	 * @param int $id Role ID.
	 * @return \Cake\Http\Response|null
	 */
	public function delete(int $id): ?Response {
		$this->request->allowMethod(['post', 'delete']);

		if (!$this->roleSource->isManaged()) {
			$this->Flash->error(__('Roles are managed externally and cannot be deleted here.'));

			return $this->redirect(['action' => 'index']);
		}

		$rolesTable = $this->fetchTable('TinyAuthBackend.Roles');
		$role = $rolesTable->get($id);

		if ($rolesTable->delete($role)) {
			$this->roleSource->clearCache();
			$this->Flash->success(__('Role deleted.'));
		} else {
			$this->Flash->error(__('Could not delete role.'));
		}

		return $this->redirect(['action' => 'index']);
	}

	/**
	 * @return \Cake\Http\Response|null
	 */
	public function reorder(): ?Response {
		$this->request->allowMethod(['post']);

		if (!$this->roleSource->isManaged()) {
			return $this->response->withType('application/json')
				->withStringBody(json_encode(['success' => false, 'error' => 'Roles are managed externally']));
		}

		$roleId = (int)$this->request->getData('role_id');
		$newParentId = $this->request->getData('parent_id');
		$newOrder = (int)$this->request->getData('sort_order');

		$rolesTable = $this->fetchTable('TinyAuthBackend.Roles');
		$role = $rolesTable->get($roleId);

		$role->parent_id = $newParentId ?: null;
		$role->sort_order = $newOrder;

		if ($rolesTable->save($role)) {
			$this->roleSource->clearCache();

			return $this->response->withType('application/json')
				->withStringBody(json_encode(['success' => true]));
		}

		return $this->response->withType('application/json')
			->withStringBody(json_encode(['success' => false, 'error' => 'Failed to save']));
	}

}
