<?php
declare(strict_types=1);

namespace TinyAuthBackend\Controller\Admin;

use Cake\Http\Response;
use TinyAuthBackend\Service\ControllerSyncService;
use TinyAuthBackend\Service\ResourceSyncService;

class SyncController extends AppController {

	/**
	 * @return \Cake\Http\Response|null
	 */
	public function controllers(): ?Response {
		$service = new ControllerSyncService();

		if ($this->request->is('post')) {
			$result = $service->sync([
				'addNew' => (bool)$this->request->getData('add_new', true),
				'addActions' => (bool)$this->request->getData('add_actions', true),
			]);

			$this->Flash->success(__(
				'Sync complete: {0} controllers added, {1} actions added.',
				$result['added'],
				$result['actions_added'],
			));

			return $this->redirect(['controller' => 'Acl', 'action' => 'index']);
		}

		$scanned = $service->scan();

		// Compare with existing
		$controllersTable = $this->fetchTable('TinyAuthBackend.TinyauthControllers');
		$existing = $controllersTable->find()
			->contain(['Actions'])
			->all()
			->toArray();

		$existingKeys = [];
		/** @var \TinyAuthBackend\Model\Entity\TinyauthController $c */
		foreach ($existing as $c) {
			$key = ($c->plugin ?? '') . '/' . ($c->prefix ?? '') . '/' . $c->name;
			$existingKeys[$key] = $c;
		}

		$diff = [];
		foreach ($scanned as $item) {
			$key = ($item['plugin'] ?? '') . '/' . ($item['prefix'] ?? '') . '/' . $item['name'];
			$status = isset($existingKeys[$key]) ? 'existing' : 'new';
			$diff[] = array_merge($item, ['status' => $status]);
		}

		$this->set(compact('diff'));

		return null;
	}

	/**
	 * @return \Cake\Http\Response|null
	 */
	public function resources(): ?Response {
		$service = new ResourceSyncService();

		if ($this->request->is('post')) {
			$result = $service->sync([
				'addNew' => (bool)$this->request->getData('add_new', true),
				'addDefaultAbilities' => (bool)$this->request->getData('add_abilities', true),
			]);

			$this->Flash->success(__(
				'Sync complete: {0} resources added, {1} abilities added.',
				$result['added'],
				$result['abilities_added'],
			));

			return $this->redirect(['controller' => 'Resources', 'action' => 'index']);
		}

		$scanned = $service->scan();

		// Compare with existing
		$resourcesTable = $this->fetchTable('TinyAuthBackend.Resources');
		$existing = $resourcesTable->find()->all()->toArray();
		$existingNames = array_column($existing, 'name');

		$diff = [];
		foreach ($scanned as $item) {
			$status = in_array($item['name'], $existingNames, true) ? 'existing' : 'new';
			$diff[] = array_merge($item, ['status' => $status]);
		}

		$this->set(compact('diff'));

		return null;
	}

}
