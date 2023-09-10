<?php

namespace TinyAuthBackend\Utility;

use Cake\Datasource\ModelAwareTrait;
use TinyAuth\Auth\AclAdapter\IniAclAdapter;
use TinyAuth\Auth\AllowAdapter\IniAllowAdapter;
use TinyAuth\Utility\TinyAuth;
use TinyAuthBackend\Model\Entity\AclRule;
use TinyAuthBackend\Model\Entity\AllowRule;

class Importer {

	use ModelAwareTrait;

	/**
	 * @param string $file
	 *
	 * @return void
	 */
	public function importAllow($file) {
		$config = [
			'filePath' => dirname($file) . DS,
			'file' => basename($file),
		];
		$allowData = (new IniAllowAdapter())->getAllow($config);

		foreach ($allowData as $allowRow) {
			foreach ($allowRow['allow'] as $action) {
				$data = [
					'type' => AllowRule::TYPE_ALLOW,
					'path' => $this->path($action, $allowRow),
				];
				$this->addAllow($data);
			}
			foreach ($allowRow['deny'] as $action) {
				$data = [
					'type' => AllowRule::TYPE_DENY,
					'path' => $this->path($action, $allowRow),
				];
				$this->addAllow($data);
			}
		}
	}

	/**
	 * @param string $action
	 * @param array $row
	 *
	 * @return string
	 */
	protected function path($action, array $row) {
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
	 * @param array $data
	 *
	 * @return void
	 */
	protected function addAllow(array $data) {
		$AllowRules = $this->fetchModel('TinyAuthBackend.AllowRules');

		$allowRule = $AllowRules->newEntity($data);
		$AllowRules->save($allowRule);
	}

	/**
	 * @param string $file
	 *
	 * @return void
	 */
	public function importAcl($file) {
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
					$data = [
						'type' => AclRule::TYPE_ALLOW,
						'path' => $this->path($action, $aclRow),
						'role' => $role,
					];
					$this->addAcl($data);
				}
			}
			foreach ($aclRow['deny'] as $action => $roles) {
				// Remap to * for simplicity
				if (count($roles) === count($availableRoles)) {
					$roles = ['*' => '*'];
				}

				foreach ($roles as $role => $id) {
					$data = [
						'type' => AclRule::TYPE_DENY,
						'path' => $this->path($action, $aclRow),
						'role' => $role,
					];
					$this->addAcl($data);
				}
			}
		}
	}

	/**
	 * @param array $data
	 *
	 * @return void
	 */
	protected function addAcl(array $data) {
		$AclRules = $this->fetchModel('TinyAuthBackend.AclRules');

		$aclRule = $AclRules->newEntity($data);
		$AclRules->save($aclRule);
	}

	/**
	 * Initialize ACL for specific role to have initial access to the backend.
	 *
	 * @param string $role
	 *
	 * @return void
	 */
	public function initializeAcl($role) {
		$paths = [
			'TinyAuthBackend.Auth::*',
			'TinyAuthBackend.Allow::*',
			'TinyAuthBackend.Acl::*',
		];

		/** @var \TinyAuthBackend\Model\Table\AclRulesTable $AclRules */
		$AclRules = $this->fetchModel('TinyAuthBackend.AclRules');
		foreach ($paths as $path) {
			$aclRule = $AclRules->newEntity([
				'type' => AclRule::TYPE_ALLOW,
				'role' => $role,
				'path' => $path,
			]);
			$AclRules->saveOrFail($aclRule);
		}
	}

}
