<?php
declare(strict_types=1);

namespace TinyAuthBackend\Controller\Admin;

use Cake\Http\Exception\BadRequestException;
use Cake\Http\Response;
use TinyAuthBackend\Service\RoleSourceService;

class ResourcesController extends AppController {

	/**
	 * @return void
	 */
	public function index(): void {
		$resourcesTable = $this->fetchTable('TinyAuthBackend.Resources');
		$scopesTable = $this->fetchTable('TinyAuthBackend.Scopes');

		$query = $resourcesTable->find()
			->contain(['ResourceAbilities'])
			->orderBy(['name' => 'ASC']);

		// Filter to App namespace by default (configurable)
		$namespaceFilter = \Cake\Core\Configure::read('TinyAuthBackend.resourceNamespaceFilter') ?? 'App\\';
		if ($namespaceFilter) {
			$query->where(['entity_class LIKE' => $namespaceFilter . '%']);
		}

		$resources = $query->all()->toArray();

		$roles = (new RoleSourceService())->getRoleEntities();
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

				/** @var \TinyAuthBackend\Model\Entity\ResourceAcl $perm */
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
		$scopeIdRaw = $this->request->getData('scope_id');
		$scopeId = $scopeIdRaw !== '' && $scopeIdRaw !== null && is_numeric($scopeIdRaw) ? (int)$scopeIdRaw : null;

		// Validate type
		if (!in_array($type, ['none', 'allow', 'deny'], true)) {
			throw new BadRequestException('Invalid permission type');
		}
		if (!in_array($roleId, array_values((new RoleSourceService())->getRoles()), true)) {
			throw new BadRequestException('Invalid role');
		}

		// Validate scope exists if provided
		if ($scopeId !== null) {
			$scopesTable = $this->fetchTable('TinyAuthBackend.Scopes');
			if (!$scopesTable->exists(['id' => $scopeId])) {
				throw new BadRequestException('Invalid scope');
			}
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
			/** @var \TinyAuthBackend\Model\Entity\ResourceAcl|null $existing */
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
		$scope = $scopeId ? $scopesTable->find()->where(['id' => $scopeId])->first() : null;

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
		$name = trim((string)$this->request->getData('name'));

		if ($name === '') {
			$this->Flash->error(__('Ability name is required.'));

			return $this->redirect(['action' => 'index', '?' => ['resource_id' => $resourceId]]);
		}

		$abilitiesTable = $this->fetchTable('TinyAuthBackend.ResourceAbilities');

		// Check for duplicate
		$exists = $abilitiesTable->exists(['resource_id' => $resourceId, 'name' => $name]);
		if ($exists) {
			$this->Flash->error(__('Ability "{0}" already exists for this resource.', $name));

			return $this->redirect(['action' => 'index', '?' => ['resource_id' => $resourceId]]);
		}

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

	/**
	 * @param int $id Ability ID.
	 * @return \Cake\Http\Response|null
	 */
	public function deleteAbility(int $id): ?Response {
		$this->request->allowMethod(['post', 'delete']);

		/** @var \TinyAuthBackend\Model\Table\ResourceAbilitiesTable $abilitiesTable */
		$abilitiesTable = $this->fetchTable('TinyAuthBackend.ResourceAbilities');
		$ability = $abilitiesTable->get($id);
		$resourceId = $ability->resource_id;

		// Check if ability has permissions
		$aclTable = $this->fetchTable('TinyAuthBackend.ResourceAcl');
		$usageCount = $aclTable->find()->where(['resource_ability_id' => $id])->count();
		if ($usageCount > 0) {
			$this->Flash->error(__('Cannot delete ability. It has {0} permission(s) assigned.', $usageCount));

			return $this->redirect(['action' => 'index', '?' => ['resource_id' => $resourceId]]);
		}

		if ($abilitiesTable->delete($ability)) {
			$this->Flash->success(__('Ability deleted.'));
		} else {
			$this->Flash->error(__('Could not delete ability.'));
		}

		return $this->redirect(['action' => 'index', '?' => ['resource_id' => $resourceId]]);
	}

}
