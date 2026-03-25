<?php
declare(strict_types=1);

namespace TinyAuthBackend\Controller\Admin;

use Cake\Http\Response;
use TinyAuthBackend\Service\RoleSourceService;

class RolesController extends AppController {

	protected RoleSourceService $roleSource;

	/**
	 * @return void
	 */
	public function initialize(): void {
		parent::initialize();
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
			/** @var \TinyAuthBackend\Model\Table\RolesTable $rolesTable */
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
			$data = $this->request->getData() + ['id' => $id];
			$role = $rolesTable->patchEntity($role, $data);
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

		// Check for child roles
		$childCount = $rolesTable->find()->where(['parent_id' => $id])->count();
		if ($childCount > 0) {
			$this->Flash->error(__('Cannot delete role. It has {0} child role(s).', $childCount));

			return $this->redirect(['action' => 'index']);
		}

		// Check for ACL permissions using this role
		$aclTable = $this->fetchTable('TinyAuthBackend.AclPermissions');
		$aclCount = $aclTable->find()->where(['role_id' => $id])->count();
		if ($aclCount > 0) {
			$this->Flash->error(__('Cannot delete role. It has {0} ACL permission(s).', $aclCount));

			return $this->redirect(['action' => 'index']);
		}

		// Check for resource permissions using this role
		$resourceAclTable = $this->fetchTable('TinyAuthBackend.ResourceAcl');
		$resourceCount = $resourceAclTable->find()->where(['role_id' => $id])->count();
		if ($resourceCount > 0) {
			$this->Flash->error(__('Cannot delete role. It has {0} resource permission(s).', $resourceCount));

			return $this->redirect(['action' => 'index']);
		}

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
				->withStringBody((string)json_encode(['success' => false, 'error' => 'Roles are managed externally']));
		}

		$roleId = (int)$this->request->getData('role_id');
		$newParentId = $this->request->getData('parent_id') ? (int)$this->request->getData('parent_id') : null;
		$newOrder = (int)$this->request->getData('sort_order');

		/** @var \TinyAuthBackend\Model\Table\RolesTable $rolesTable */
		$rolesTable = $this->fetchTable('TinyAuthBackend.Roles');
		$role = $rolesTable->get($roleId);

		$role = $rolesTable->patchEntity($role, [
			'id' => $roleId,
			'parent_id' => $newParentId ?: null,
			'sort_order' => $newOrder,
		]);

		if ($role->hasErrors()) {
			$parentErrors = $role->getError('parent_id');
			$error = $parentErrors ? implode(' ', array_map('strval', $parentErrors)) : 'Invalid role hierarchy';

			return $this->response->withType('application/json')
				->withStringBody((string)json_encode(['success' => false, 'error' => $error]));
		}

		if ($rolesTable->save($role)) {
			$this->roleSource->clearCache();

			return $this->response->withType('application/json')
				->withStringBody((string)json_encode(['success' => true]));
		}

		return $this->response->withType('application/json')
			->withStringBody((string)json_encode(['success' => false, 'error' => 'Failed to save']));
	}

}
