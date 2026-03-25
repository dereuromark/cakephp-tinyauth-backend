<?php
declare(strict_types=1);

namespace TinyAuthBackend\Controller\Admin;

use Cake\Http\Exception\BadRequestException;
use Cake\Http\Response;
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

			// Load permissions for this controller's actions
			$permissionsTable = $this->fetchTable('TinyAuthBackend.AclPermissions');
			$actionIds = array_map(fn ($a) => $a->id, $actions);

			if ($actionIds) {
				$perms = $permissionsTable->find()
					->where(['action_id IN' => $actionIds])
					->all();

				/** @var \TinyAuthBackend\Model\Entity\AclPermission $perm */
				foreach ($perms as $perm) {
					$permissions[$perm->action_id][$perm->role_id] = $perm->type;
				}
			}
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
				if (!$permissionsTable->save($existing)) {
					$this->response = $this->response->withStatus(500);
					$this->set('error', 'Failed to update permission');
				}
			} else {
				$permission = $permissionsTable->newEntity([
					'action_id' => $actionId,
					'role_id' => $roleId,
					'type' => $type,
				]);
				if (!$permissionsTable->save($permission)) {
					$this->response = $this->response->withStatus(500);
					$this->set('error', 'Failed to save permission');
				}
			}
		}

		// Return updated cell HTML
		$this->viewBuilder()->disableAutoLayout();
		$this->set(compact('actionId', 'roleId', 'type'));

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

}
