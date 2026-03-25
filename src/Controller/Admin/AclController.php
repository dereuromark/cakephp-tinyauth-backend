<?php
declare(strict_types=1);

namespace TinyAuthBackend\Controller\Admin;

use Cake\Controller\Controller;
use Cake\Http\Response;

/**
 * @property \TinyAuthBackend\Model\Table\TinyauthControllersTable $TinyauthControllers
 */
class AclController extends Controller {

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
		$controllersTable = $this->fetchTable('TinyAuthBackend.TinyauthControllers');
		$rolesTable = $this->fetchTable('TinyAuthBackend.Roles');

		$tree = $controllersTable->findTree();
		$roles = $rolesTable->find()->orderBy(['sort_order' => 'ASC'])->all()->toArray();

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

		$permissionsTable = $this->fetchTable('TinyAuthBackend.AclPermissions');

		$existing = $permissionsTable->find()
			->where(['action_id' => $actionId, 'role_id' => $roleId])
			->first();

		if ($type === 'none') {
			if ($existing) {
				$permissionsTable->delete($existing);
			}
		} else {
			if ($existing) {
				$existing->type = $type;
				$permissionsTable->save($existing);
			} else {
				$permission = $permissionsTable->newEntity([
					'action_id' => $actionId,
					'role_id' => $roleId,
					'type' => $type,
				]);
				$permissionsTable->save($permission);
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
		$results = ['controllers' => [], 'actions' => [], 'roles' => []];

		if (strlen($q) >= 2) {
			$controllersTable = $this->fetchTable('TinyAuthBackend.TinyauthControllers');
			$actionsTable = $this->fetchTable('TinyAuthBackend.Actions');
			$rolesTable = $this->fetchTable('TinyAuthBackend.Roles');

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

			$results['roles'] = $rolesTable->find()
				->where(['OR' => ['name LIKE' => "%{$q}%", 'alias LIKE' => "%{$q}%"]])
				->limit(5)
				->all()
				->toArray();
		}

		$this->set('results', $results);
	}

}
