<?php
declare(strict_types=1);

namespace TinyAuthBackend\Controller\Admin;

use Cake\Cache\Cache;
use Cake\Controller\Controller;
use Cake\Http\Response;

/**
 * @property \TinyAuthBackend\Model\Table\TinyauthControllersTable $TinyauthControllers
 */
class AllowController extends Controller {

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

		$filter = $this->request->getQuery('filter', 'all');

		$query = $controllersTable->find()
			->contain([
				'Actions' => function (\Cake\ORM\Query\SelectQuery $q) use ($filter) {
					if ($filter === 'public') {
						return $q->where(['Actions.is_public' => true]);
					}
					if ($filter === 'protected') {
						return $q->where(['Actions.is_public' => false]);
					}

					return $q;
				},
			])
			->orderBy(['plugin' => 'ASC', 'prefix' => 'ASC', 'name' => 'ASC']);

		$controllers = $query->all()->toArray();

		$this->set(compact('controllers', 'filter'));
	}

	/**
	 * @return \Cake\Http\Response|null
	 */
	public function toggle(): ?Response {
		$this->request->allowMethod(['post']);

		$actionId = (int)$this->request->getData('action_id');
		$isPublic = (bool)$this->request->getData('is_public');

		if (!$actionId) {
			$this->response = $this->response->withStatus(400);
			$this->viewBuilder()->disableAutoLayout();
			$this->set('error', 'Invalid action ID');

			return $this->render('toggle_cell');
		}

		/** @var \TinyAuthBackend\Model\Table\ActionsTable $actionsTable */
		$actionsTable = $this->fetchTable('TinyAuthBackend.Actions');
		$action = $actionsTable->get($actionId);

		$action->is_public = $isPublic;

		if (!$actionsTable->save($action)) {
			$this->response = $this->response->withStatus(500);
			$this->set('error', 'Failed to update action');
		}

		$this->viewBuilder()->disableAutoLayout();
		$this->set(compact('action'));

		return $this->render('toggle_cell');
	}

	/**
	 * @return \Cake\Http\Response|null
	 */
	public function bulkToggle(): ?Response {
		$this->request->allowMethod(['post']);

		$controllerId = (int)$this->request->getData('controller_id');
		$isPublic = (bool)$this->request->getData('is_public');

		if (!$controllerId) {
			$this->Flash->error(__('Invalid controller.'));

			return $this->redirect(['action' => 'index']);
		}

		$actionsTable = $this->fetchTable('TinyAuthBackend.Actions');
		$actionsTable->updateAll(
			['is_public' => $isPublic],
			['controller_id' => $controllerId],
		);

		Cache::delete('TinyAuth.allow');

		$this->Flash->success(__('Actions updated.'));

		return $this->redirect(['action' => 'index']);
	}

}
