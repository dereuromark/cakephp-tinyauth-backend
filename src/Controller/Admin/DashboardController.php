<?php
declare(strict_types=1);

namespace TinyAuthBackend\Controller\Admin;

use TinyAuthBackend\Service\FeatureService;

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

		// Check enabled features via service (auto-detects from database tables)
		$featureService = new FeatureService();
		$features = $featureService->getEnabledFeatures();

		// Get resource stats if enabled
		if ($features['resources']) {
			$resourcesTable = $this->fetchTable('TinyAuthBackend.Resources');
			$resourceAbilitiesTable = $this->fetchTable('TinyAuthBackend.ResourceAbilities');
			$resourceAclTable = $this->fetchTable('TinyAuthBackend.ResourceAcl');
			$stats['resources'] = $resourcesTable->find()->count();
			$stats['abilities'] = $resourceAbilitiesTable->find()->count();
			$stats['resource_permissions'] = $resourceAclTable->find()->count();
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
