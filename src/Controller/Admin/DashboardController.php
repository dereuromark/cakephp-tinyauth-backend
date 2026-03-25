<?php
declare(strict_types=1);

namespace TinyAuthBackend\Controller\Admin;

use Cake\Core\Configure;

class DashboardController extends AppController {

	/**
	 * @return void
	 */
	public function index(): void {
		// Gather statistics
		$controllersTable = $this->fetchTable('TinyAuthBackend.TinyauthControllers');
		$actionsTable = $this->fetchTable('TinyAuthBackend.Actions');
		$rolesTable = $this->fetchTable('TinyAuthBackend.Roles');
		$aclPermissionsTable = $this->fetchTable('TinyAuthBackend.AclPermissions');

		$stats = [
			'controllers' => $controllersTable->find()->count(),
			'actions' => $actionsTable->find()->count(),
			'public_actions' => $actionsTable->find()->where(['is_public' => true])->count(),
			'roles' => $rolesTable->find()->count(),
			'acl_permissions' => $aclPermissionsTable->find()->count(),
		];

		// Check enabled features
		$features = [
			'acl' => (bool)Configure::read('TinyAuthBackend.features.acl', true),
			'allow' => (bool)Configure::read('TinyAuthBackend.features.allow', true),
			'resources' => (bool)Configure::read('TinyAuthBackend.features.resources', false),
			'scopes' => (bool)Configure::read('TinyAuthBackend.features.scopes', false),
		];

		// Get resource stats if enabled
		if ($features['resources']) {
			$resourcesTable = $this->fetchTable('TinyAuthBackend.Resources');
			$abilitiesTable = $this->fetchTable('TinyAuthBackend.Abilities');
			$resourcePermissionsTable = $this->fetchTable('TinyAuthBackend.ResourcePermissions');
			$stats['resources'] = $resourcesTable->find()->count();
			$stats['abilities'] = $abilitiesTable->find()->count();
			$stats['resource_permissions'] = $resourcePermissionsTable->find()->count();
		}

		if ($features['scopes']) {
			$scopesTable = $this->fetchTable('TinyAuthBackend.Scopes');
			$stats['scopes'] = $scopesTable->find()->count();
		}

		// Recent activity - last 5 controllers synced
		$recentControllers = $controllersTable->find()
			->orderBy(['modified' => 'DESC'])
			->limit(5)
			->all()
			->toArray();

		$this->set(compact('stats', 'features', 'recentControllers'));
	}

}
