<?php
declare(strict_types=1);

namespace TinyAuthBackend\Utility;

use Cake\Datasource\ModelAwareTrait;
use TinyAuth\Auth\AclAdapter\IniAclAdapter;
use TinyAuth\Auth\AllowAdapter\IniAllowAdapter;
use TinyAuth\Utility\TinyAuth;
use TinyAuthBackend\Service\RoleSourceService;

class Importer {

	use ModelAwareTrait;

	/**
	 * @param string $file
	 *
	 * @return void
	 */
	public function importAllow(string $file): void {
		$config = [
			'filePath' => dirname($file) . DS,
			'file' => basename($file),
		];
		$allowData = (new IniAllowAdapter())->getAllow($config);

		foreach ($allowData as $allowRow) {
			foreach ($allowRow['allow'] as $action) {
				[$controllerId, $actionId] = $this->ensureAction($allowRow, $action);
				$this->setPublicFlag($actionId, true);
			}
			foreach ($allowRow['deny'] as $action) {
				[$controllerId, $actionId] = $this->ensureAction($allowRow, $action);
				$this->setPublicFlag($actionId, false);
			}
		}
	}

	/**
	 * @param string $action
	 * @param array<string, mixed> $row
	 *
	 * @return string
	 */
	protected function path(string $action, array $row): string {
		$controller = $row['controller'];
		if ($row['prefix']) {
			$controller = $row['prefix'] . '/' . $controller;
		}
		if ($row['plugin']) {
			$controller = $row['plugin'] . '.' . $controller;
		}

		return $controller . '::' . $action;
	}

	/**
	 * @param string $file
	 * @return void
	 */
	public function importAcl(string $file): void {
		$config = [
			'filePath' => dirname($file) . DS,
			'file' => basename($file),
		];
		$availableRoles = (new TinyAuth())->getAvailableRoles();
		$aclData = (new IniAclAdapter())->getAcl($availableRoles, $config);

		foreach ($aclData as $aclRow) {
			foreach ($aclRow['allow'] as $action => $roles) {
				// Remap to * for simplicity
				if (count($roles) === count($availableRoles)) {
					$roles = ['*' => '*'];
				}

				foreach ($roles as $role => $id) {
					[$controllerId, $actionId] = $this->ensureAction($aclRow, $action);
					$this->upsertAclPermission($actionId, $role, 'allow');
				}
			}
			foreach ($aclRow['deny'] as $action => $roles) {
				// Remap to * for simplicity
				if (count($roles) === count($availableRoles)) {
					$roles = ['*' => '*'];
				}

				foreach ($roles as $role => $id) {
					[$controllerId, $actionId] = $this->ensureAction($aclRow, $action);
					$this->upsertAclPermission($actionId, $role, 'deny');
				}
			}
		}
	}

	/**
	 * Initialize ACL for specific role to have initial access to the backend.
	 *
	 * @param string $roleAlias
	 * @return void
	 */
	public function initializeAcl(string $roleAlias): void {
		$roleId = (new RoleSourceService())->getRoles()[$roleAlias] ?? null;
		if ($roleId === null) {
			return;
		}

		$controllerSyncService = new \TinyAuthBackend\Service\ControllerSyncService();
		$controllerSyncService->sync();

		/** @var \TinyAuthBackend\Model\Table\TinyauthControllersTable $controllersTable */
		$controllersTable = $this->fetchModel('TinyAuthBackend.TinyauthControllers');
		$controllers = $controllersTable->find()
			->contain(['Actions'])
			->where([
				'plugin' => 'TinyAuthBackend',
				'prefix' => 'Admin',
			])
			->all();

		/** @var \TinyAuthBackend\Model\Entity\TinyauthController $controller */
		foreach ($controllers as $controller) {
			foreach ($controller->actions as $action) {
				$this->upsertAclPermission($action->id, $roleAlias, 'allow');
			}
		}
	}

	/**
	 * @param array<string, mixed> $row
	 * @param string $actionName
	 * @return array{0: int, 1: int}
	 */
	protected function ensureAction(array $row, string $actionName): array {
		/** @var \TinyAuthBackend\Model\Table\TinyauthControllersTable $controllersTable */
		$controllersTable = $this->fetchModel('TinyAuthBackend.TinyauthControllers');
		/** @var \TinyAuthBackend\Model\Table\ActionsTable $actionsTable */
		$actionsTable = $this->fetchModel('TinyAuthBackend.Actions');

		$controller = $controllersTable->find()
			->where([
				'plugin IS' => $row['plugin'],
				'prefix IS' => $row['prefix'],
				'name' => $row['controller'],
			])
			->first();

		if (!$controller) {
			$controller = $controllersTable->newEntity([
				'plugin' => $row['plugin'],
				'prefix' => $row['prefix'],
				'name' => $row['controller'],
			]);
			$controllersTable->saveOrFail($controller);
		}

		$action = $actionsTable->find()
			->where([
				'controller_id' => $controller->id,
				'name' => $actionName,
			])
			->first();

		if (!$action) {
			$action = $actionsTable->newEntity([
				'controller_id' => $controller->id,
				'name' => $actionName,
				'is_public' => false,
			]);
			$actionsTable->saveOrFail($action);
		}

		return [$controller->id, $action->id];
	}

	/**
	 * @param int $actionId
	 * @param bool $isPublic
	 * @return void
	 */
	protected function setPublicFlag(int $actionId, bool $isPublic): void {
		/** @var \TinyAuthBackend\Model\Table\ActionsTable $actionsTable */
		$actionsTable = $this->fetchModel('TinyAuthBackend.Actions');
		$action = $actionsTable->get($actionId);
		$action->is_public = $isPublic;
		$actionsTable->saveOrFail($action);
	}

	/**
	 * @param int $actionId
	 * @param string $roleAlias
	 * @param string $type
	 * @return void
	 */
	protected function upsertAclPermission(int $actionId, string $roleAlias, string $type): void {
		$roleId = (new RoleSourceService())->getRoles()[$roleAlias] ?? null;
		if ($roleId === null || $roleAlias === '*') {
			return;
		}

		/** @var \TinyAuthBackend\Model\Table\AclPermissionsTable $permissionsTable */
		$permissionsTable = $this->fetchModel('TinyAuthBackend.AclPermissions');
		$permission = $permissionsTable->find()
			->where(['action_id' => $actionId, 'role_id' => $roleId])
			->first();

		if (!$permission) {
			$permission = $permissionsTable->newEntity([
				'action_id' => $actionId,
				'role_id' => $roleId,
			]);
		}

		$permission->type = $type;
		$permissionsTable->saveOrFail($permission);
	}

}
