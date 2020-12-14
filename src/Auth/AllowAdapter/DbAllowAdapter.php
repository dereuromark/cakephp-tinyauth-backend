<?php

namespace TinyAuthBackend\Auth\AllowAdapter;

use Cake\Datasource\ModelAwareTrait;
use TinyAuthBackend\Model\Entity\AllowRule;
use TinyAuthBackend\Utility\RulePath;
use TinyAuth\Auth\AllowAdapter\AllowAdapterInterface;

/**
 * @property \TinyAuthBackend\Model\Table\AllowRulesTable $AllowRules
 */
class DbAllowAdapter implements AllowAdapterInterface {

	use ModelAwareTrait;

	/**
	 * @var string[]
	 */
	protected static $typeMap = [
		AllowRule::TYPE_ALLOW => 'allow',
		AllowRule::TYPE_DENY => 'deny',
	];

	/**
	 * {@inheritDoc}
	 *
	 * @return array
	 */
	public function getAllow(array $config) {
		$allow = [];

		$allowRules = $this->getRules();
		foreach ($allowRules as $allowRule) {
			$array = RulePath::parse($allowRule->path);
			$action = $array['action'];
			unset($array['action']);
			$key = RulePath::key($array);

			$ruleType = static::$typeMap[$allowRule->type];
			if (!isset($allow[$key])) {
				$allow[$key] = $this->buildArray($key);
				foreach (static::$typeMap as $type) {
					$allow[$key][$type] = [];
				}
			}
			$allow[$key][$ruleType][] = $action;
		}

		return $allow;
	}

	/**
	 * @return \TinyAuthBackend\Model\Entity\AllowRule[]
	 */
	protected function getRules() {
		$this->loadModel('TinyAuthBackend.AllowRules');
		return $this->AllowRules->find()
			->select(['type', 'path'])
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

}
