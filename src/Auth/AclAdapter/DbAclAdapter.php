<?php

namespace TinyAuthBackend\Auth\AclAdapter;

use Cake\Datasource\ModelAwareTrait;
use TinyAuthBackend\Model\Entity\AclRule;
use TinyAuth\Auth\AclAdapter\AclAdapterInterface;

/**
 * @property \TinyAuthBackend\Model\Table\AclRulesTable $AclRules
 */
class DbAclAdapter implements AclAdapterInterface {

	use ModelAwareTrait;

	/**
	 * @var string[]
	 */
	protected static $typeMap = [
		AclRule::TYPE_ALLOW => 'allow',
		AclRule::TYPE_DENY => 'deny',
	];

	/**
	 * {@inheritDoc}
	 *
	 * @return array
	 */
	public function getAcl(array $availableRoles, array $config) {
		$acl = [];

		$aclRules = $this->getRules();
		foreach ($aclRules as $aclRule) {
			list($key, $action) = explode('::', $aclRule->path);
			$ruleType = static::$typeMap[$aclRule->type];
			if (!isset($acl[$key])) {
				$acl[$key] = $this->buildArray($key);
				foreach (static::$typeMap as $type) {
					$acl[$key][$type] = [];
				}
			}
			$acl[$key][$ruleType][$action] = $this->buildRoleArray($availableRoles, $aclRule->role);
		}

		return $acl;
	}

	/**
	 * @return \TinyAuthBackend\Model\Entity\AclRule[]
	 */
	protected function getRules() {
		$this->loadModel('TinyAuthBackend.AclRules');
		return $this->AclRules->find()
			->select(['type', 'role', 'path'])
			->all()
			->toArray();
	}

	/**
	 * @param string $key
	 *
	 * @return string[]
	 */
	protected function buildArray($key) {
		$prefix = $plugin = null;
		if (strpos($key, '.') !== false) {
			list($plugin, $key) = explode('.', $key, 2);
		}
		if (strpos($key, '/') !== false) {
			$pos = (int)strrpos($key, '/');
			$prefix = substr($key, 0, $pos);
			$key = substr($key, $pos + 1);
		}

		return [
			'plugin' => $plugin,
			'prefix' => $prefix,
			'controller' => $key,
		];
	}

	/**
	 * @param int[] $availableRoles
	 * @param string $role
	 *
	 * @return int[]
	 */
	protected function buildRoleArray(array $availableRoles, $role) {
		if (isset($availableRoles[$role])) {
			return [
				$role => $availableRoles[$role],
			];
		}

		if ($role !== '*') {
			return [];
		}

		return $availableRoles;
	}

}
