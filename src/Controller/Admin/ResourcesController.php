<?php
declare(strict_types=1);

namespace TinyAuthBackend\Controller\Admin;

use Cake\Controller\Controller;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Response;

class ResourcesController extends Controller {

	/**
	 * @return void
	 */
	public function initialize(): void {
		parent::initialize();
		$this->viewBuilder()->setLayout('TinyAuthBackend.tinyauth');
	}

	/**
	 * @return void
	 */
	public function index(): void {
		$resourcesTable = $this->fetchTable('TinyAuthBackend.Resources');
		$rolesTable = $this->fetchTable('TinyAuthBackend.Roles');
		$scopesTable = $this->fetchTable('TinyAuthBackend.Scopes');

		$resources = $resourcesTable->find()
			->contain(['ResourceAbilities'])
			->orderBy(['name' => 'ASC'])
			->all()
			->toArray();

		$roles = $rolesTable->find()->orderBy(['sort_order' => 'ASC'])->all()->toArray();
		$scopes = $scopesTable->find()->orderBy(['name' => 'ASC'])->all()->toArray();

		// Get selected resource
		$resourceId = $this->request->getQuery('resource_id');
		$selectedResource = null;
		$abilities = [];
		$permissions = [];

		if ($resourceId) {
			$selectedResource = $resourcesTable->get((int)$resourceId, contain: ['ResourceAbilities']);
			$abilities = $selectedResource->resource_abilities ?? [];

			// Load permissions
			$resourceAclTable = $this->fetchTable('TinyAuthBackend.ResourceAcl');
			$abilityIds = array_map(fn ($a) => $a->id, $abilities);

			if ($abilityIds) {
				$perms = $resourceAclTable->find()
					->contain(['Scopes'])
					->where(['resource_ability_id IN' => $abilityIds])
					->all();

				foreach ($perms as $perm) {
					$permissions[$perm->resource_ability_id][$perm->role_id] = [
						'type' => $perm->type,
						'scope_id' => $perm->scope_id,
						'scope_name' => $perm->scope?->name,
					];
				}
			}
		}

		$this->set(compact('resources', 'roles', 'scopes', 'selectedResource', 'abilities', 'permissions'));
	}

	/**
	 * @return \Cake\Http\Response|null
	 */
	public function toggle(): ?Response {
		$this->request->allowMethod(['post']);

		$abilityId = (int)$this->request->getData('ability_id');
		$roleId = (int)$this->request->getData('role_id');
		$type = $this->request->getData('type');
		$scopeId = $this->request->getData('scope_id') ?: null;

		// Validate type
		if (!in_array($type, ['none', 'allow', 'deny'], true)) {
			throw new BadRequestException('Invalid permission type');
		}

		$resourceAclTable = $this->fetchTable('TinyAuthBackend.ResourceAcl');

		$existing = $resourceAclTable->find()
			->where(['resource_ability_id' => $abilityId, 'role_id' => $roleId])
			->first();

		if ($type === 'none') {
			if ($existing) {
				if (!$resourceAclTable->delete($existing)) {
					$this->response = $this->response->withStatus(500);
					$this->set('error', 'Failed to remove permission');
				}
			}
		} else {
			if ($existing) {
				$existing->type = $type;
				$existing->scope_id = $scopeId;
				if (!$resourceAclTable->save($existing)) {
					$this->response = $this->response->withStatus(500);
					$this->set('error', 'Failed to update permission');
				}
			} else {
				$permission = $resourceAclTable->newEntity([
					'resource_ability_id' => $abilityId,
					'role_id' => $roleId,
					'type' => $type,
					'scope_id' => $scopeId,
				]);
				if (!$resourceAclTable->save($permission)) {
					$this->response = $this->response->withStatus(500);
					$this->set('error', 'Failed to create permission');
				}
			}
		}

		// Return updated cell
		$scopesTable = $this->fetchTable('TinyAuthBackend.Scopes');
		$scope = $scopeId ? $scopesTable->get((int)$scopeId) : null;

		$this->viewBuilder()->disableAutoLayout();
		$this->set(compact('abilityId', 'roleId', 'type', 'scope'));

		return $this->render('toggle_cell');
	}

	/**
	 * @return \Cake\Http\Response|null
	 */
	public function addAbility(): ?Response {
		$this->request->allowMethod(['post']);

		$resourceId = (int)$this->request->getData('resource_id');
		$name = $this->request->getData('name');

		$abilitiesTable = $this->fetchTable('TinyAuthBackend.ResourceAbilities');
		$ability = $abilitiesTable->newEntity([
			'resource_id' => $resourceId,
			'name' => $name,
		]);

		if ($abilitiesTable->save($ability)) {
			$this->Flash->success(__('Ability added.'));
		} else {
			$this->Flash->error(__('Could not add ability.'));
		}

		return $this->redirect(['action' => 'index', '?' => ['resource_id' => $resourceId]]);
	}

}
