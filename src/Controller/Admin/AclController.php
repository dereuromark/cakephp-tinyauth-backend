<?php
declare(strict_types=1);

namespace TinyAuthBackend\Controller\Admin;

use Cake\Core\Configure;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Response;
use TinyAuthBackend\Service\HierarchyService;
use TinyAuthBackend\Service\RoleSourceService;

/**
 * @property \TinyAuthBackend\Model\Table\TinyauthControllersTable $TinyauthControllers
 */
class AclController extends AppController {

	/**
	 * @return void
	 */
	public function index(): void {
		/** @var \TinyAuthBackend\Model\Table\TinyauthControllersTable $controllersTable */
		$controllersTable = $this->fetchTable('TinyAuthBackend.TinyauthControllers');
		$roleSource = new RoleSourceService();

		$tree = $controllersTable->findTree();
		$roles = $roleSource->getRoleEntities();

		// Get selected controller
		$controllerId = $this->request->getQuery('controller_id');
		$selectedController = null;
		$actions = [];
		$permissions = [];

		if ($controllerId) {
			$selectedController = $controllersTable->get($controllerId, contain: ['Actions']);
			$actions = $selectedController->actions ?? [];
			$permissions = $this->buildCellStates($roles, array_map(static fn ($action) => (int)$action->id, $actions));
		}

		$this->set(compact('tree', 'roles', 'selectedController', 'actions', 'permissions'));
	}

	/**
	 * @return \Cake\Http\Response|null
	 */
	public function toggle(): ?Response {
		$this->request->allowMethod(['post']);

		$actionId = (int)$this->request->getData('action_id');
		$roleId = (int)$this->request->getData('role_id');
		$type = $this->request->getData('type');
		$description = $this->request->getData('description');
		$description = is_string($description) && $description !== '' ? $description : null;

		if (!in_array($type, ['none', 'allow', 'deny'], true)) {
			throw new BadRequestException('Invalid permission type');
		}
		if (!in_array($roleId, array_values((new RoleSourceService())->getRoles()), true)) {
			throw new BadRequestException('Invalid role');
		}

		$permissionsTable = $this->fetchTable('TinyAuthBackend.AclPermissions');

		$existing = $permissionsTable->find()
			->where(['action_id' => $actionId, 'role_id' => $roleId])
			->first();

		if ($type === 'none') {
			if ($existing) {
				if (!$permissionsTable->delete($existing)) {
					$this->response = $this->response->withStatus(500);
					$this->set('error', 'Failed to delete permission');
				}
			}
		} else {
			/** @var \TinyAuthBackend\Model\Entity\AclPermission|null $existing */
			if ($existing) {
				$existing->type = $type;
				$existing->description = $description;
				if (!$permissionsTable->save($existing)) {
					$this->response = $this->response->withStatus(500);
					$this->set('error', 'Failed to update permission');
				}
			} else {
				$permission = $permissionsTable->newEntity([
					'action_id' => $actionId,
					'role_id' => $roleId,
					'type' => $type,
					'description' => $description,
				]);
				if (!$permissionsTable->save($permission)) {
					$this->response = $this->response->withStatus(500);
					$this->set('error', 'Failed to save permission');
				}
			}
		}

		$roles = (new RoleSourceService())->getRoleEntities();
		$permissions = $this->buildCellStates($roles, [$actionId]);
		$cell = $permissions[$actionId][$roleId] ?? $this->buildCellState($actionId, $roleId, null, false);

		// Return updated cell HTML
		$this->viewBuilder()->disableAutoLayout();
		$this->set(compact('cell'));

		return $this->render('toggle_cell');
	}

	/**
	 * @return void
	 */
	public function search(): void {
		$this->viewBuilder()->disableAutoLayout();

		$q = $this->request->getQuery('q', '');
		$q = substr($q, 0, 100); // Limit search query length
		$results = ['controllers' => [], 'actions' => [], 'roles' => []];

		if (strlen($q) >= 2) {
			$controllersTable = $this->fetchTable('TinyAuthBackend.TinyauthControllers');
			$actionsTable = $this->fetchTable('TinyAuthBackend.Actions');

			$results['controllers'] = $controllersTable->find()
				->where(['name LIKE' => "%{$q}%"])
				->limit(5)
				->all()
				->toArray();

			$results['actions'] = $actionsTable->find()
				->contain(['TinyauthControllers'])
				->where(['Actions.name LIKE' => "%{$q}%"])
				->limit(5)
				->all()
				->toArray();

			$roles = (new RoleSourceService())->getRoleEntities();
			$results['roles'] = array_slice(array_values(array_filter($roles, function (object $role) use ($q): bool {
				$name = (string)($role->name ?? '');
				$alias = (string)($role->alias ?? '');

				return stripos($name, $q) !== false || stripos($alias, $q) !== false;
			})), 0, 5);
		}

		$this->set('results', $results);
	}

	/**
	 * @param array<object> $roles
	 * @param array<int> $actionIds
	 * @return array<int, array<int, array<string, mixed>>>
	 */
	protected function buildCellStates(array $roles, array $actionIds): array {
		$states = [];
		if (!$actionIds) {
			return $states;
		}

		$permissionsTable = $this->fetchTable('TinyAuthBackend.AclPermissions');
		$perms = $permissionsTable->find()
			->where(['action_id IN' => $actionIds])
			->all();

		$directPermissions = [];
		$directDescriptions = [];
		/** @var \TinyAuthBackend\Model\Entity\AclPermission $perm */
		foreach ($perms as $perm) {
			$directPermissions[$perm->action_id][$perm->role_id] = $perm->type;
			$directDescriptions[$perm->action_id][$perm->role_id] = $perm->description;
		}

		$inheritedPermissions = $this->buildInheritedPermissions($roles, $actionIds, $directPermissions);

		foreach ($actionIds as $actionId) {
			foreach ($roles as $role) {
				$roleId = (int)($role->id ?? 0);
				if (!$roleId) {
					continue;
				}

				$directType = $directPermissions[$actionId][$roleId] ?? null;
				$directDescription = $directDescriptions[$actionId][$roleId] ?? null;
				$isInherited = $inheritedPermissions[$actionId][$roleId] ?? false;
				$states[$actionId][$roleId] = $this->buildCellState($actionId, $roleId, $directType, $isInherited, $directDescription);
			}
		}

		return $states;
	}

	/**
	 * @param array<object> $roles
	 * @param array<int> $actionIds
	 * @param array<int, array<int, string>> $directPermissions
	 * @return array<int, array<int, bool>>
	 */
	protected function buildInheritedPermissions(array $roles, array $actionIds, array $directPermissions): array {
		if (!Configure::read('TinyAuthBackend.roleHierarchy')) {
			return [];
		}

		$availableRoles = [];
		$aliasesByRoleId = [];
		foreach ($roles as $role) {
			$alias = isset($role->alias) ? (string)$role->alias : '';
			$roleId = isset($role->id) ? (int)$role->id : 0;
			if ($alias === '' || !$roleId) {
				continue;
			}

			$availableRoles[$alias] = $roleId;
			$aliasesByRoleId[$roleId] = $alias;
		}

		$acl = ['selected' => ['allow' => [], 'deny' => []]];
		foreach ($actionIds as $actionId) {
			foreach (($directPermissions[$actionId] ?? []) as $roleId => $type) {
				$alias = $aliasesByRoleId[$roleId] ?? null;
				if ($alias === null) {
					continue;
				}
				$acl['selected'][$type][(string)$actionId][$alias] = $roleId;
			}
		}

		$effectiveAcl = (new HierarchyService())->applyInheritance($acl, $availableRoles);
		$inheritedPermissions = [];
		foreach ($actionIds as $actionId) {
			foreach (($effectiveAcl['selected']['allow'][(string)$actionId] ?? []) as $alias => $roleId) {
				if (($directPermissions[$actionId][$roleId] ?? null) === 'allow') {
					continue;
				}
				if (($directPermissions[$actionId][$roleId] ?? null) === 'deny') {
					continue;
				}

				$inheritedPermissions[$actionId][$roleId] = true;
			}
		}

		return $inheritedPermissions;
	}

	/**
	 * @param int $actionId
	 * @param int $roleId
	 * @param string|null $directType
	 * @param bool $isInherited
	 * @param string|null $description Optional rule description for tooltip.
	 * @return array<string, mixed>
	 */
	protected function buildCellState(int $actionId, int $roleId, ?string $directType, bool $isInherited, ?string $description = null): array {
		$suffix = $description !== null && $description !== '' ? ' — ' . $description : '';
		if ($directType === 'deny') {
			return [
				'action_id' => $actionId,
				'role_id' => $roleId,
				'class' => 'deny',
				'symbol' => '&#10005;',
				'next_type' => 'none',
				'state' => 'deny',
				'title' => 'Denied' . $suffix,
				'description' => $description,
			];
		}
		if ($directType === 'allow') {
			return [
				'action_id' => $actionId,
				'role_id' => $roleId,
				'class' => 'allow',
				'symbol' => '&#9679;',
				'next_type' => 'deny',
				'state' => 'allow',
				'title' => 'Allowed' . $suffix,
				'description' => $description,
			];
		}
		if ($isInherited) {
			return [
				'action_id' => $actionId,
				'role_id' => $roleId,
				'class' => 'allow inherited',
				'symbol' => '&#9679;',
				'next_type' => 'deny',
				'state' => 'inherited',
				'title' => 'Inherited permission',
				'description' => null,
			];
		}

		return [
			'action_id' => $actionId,
			'role_id' => $roleId,
			'class' => 'none',
			'symbol' => '&#9675;',
			'next_type' => 'allow',
			'state' => 'none',
			'title' => 'No permission',
			'description' => null,
		];
	}

}
