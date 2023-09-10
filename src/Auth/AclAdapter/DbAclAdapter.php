<?php

namespace TinyAuthBackend\Auth\AclAdapter;

use Cake\Datasource\ModelAwareTrait;
use TinyAuth\Auth\AclAdapter\AclAdapterInterface;
use TinyAuthBackend\Model\Entity\AclRule;
use TinyAuthBackend\Model\Table\AclRulesTable;

class DbAclAdapter implements AclAdapterInterface {

	use ModelAwareTrait;

	protected AclRulesTable $AclRules;

	/**
	 * @var array<string>
	 */
	protected static array $typeMap = [
		AclRule::TYPE_ALLOW => 'allow',
		AclRule::TYPE_DENY => 'deny',
	];

	/**
	 * {@inheritDoc}
	 *
	 * @return array
	 */
	public function getAcl(array $availableRoles, array $config): array {
		$acl = [];

		$aclRules = $this->getRules();
		foreach ($aclRules as $aclRule) {
			[$key, $action] = explode('::', $aclRule->path);
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
	 * @return array<\TinyAuthBackend\Model\Entity\AclRule>
	 */
	protected function getRules() {
		$AclRules = $this->fetchModel('TinyAuthBackend.AclRules');

		return $AclRules->find()
			->select(['type', 'role', 'path'])
			->all()
			->toArray();
	}

	/**
	 * @param string $key
	 *
	 * @return array
	 */
	protected function buildArray($key) {
		$prefix = $plugin = null;
		if (strpos($key, '.') !== false) {
			[$plugin, $key] = explode('.', $key, 2);
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
	 * @param array<int> $availableRoles
	 * @param string $role
	 *
	 * @return array<int>
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
