<?php

namespace TinyAuthBackend\Model\Table;

use Cake\Utility\Inflector;
use TinyAuthBackend\Utility\RulePath;
use TinyAuthBackend\Utility\Utility;

trait ValidationTrait {

	/**
	 * @param string $path
	 *
	 * @return bool
	 */
	protected function assertValidPath($path) {
		$array = RulePath::parse($path);

		if (!$array['controller'] || Utility::camelizeTokenString(Utility::underscoreTokenString($array['controller'], '/'), '/') !== $array['controller']) {
			return false;
		}
		if (!$array['action'] || Inflector::variable(Inflector::underscore($array['action'])) !== $array['action']) {
			return false;
		}

		if ($array['prefix'] && !preg_match('#[A-Za-u0-9/_]#', $array['prefix'])) {
			return false;
		}
		if ($array['plugin'] && Utility::camelizeTokenString(Utility::underscoreTokenString($array['plugin'], '/'), '/') !== $array['plugin']) {
			return false;
		}

		return true;
	}

	/**
	 * @param string $path
	 *
	 * @return string
	 */
	protected function normalizePath($path) {
		$array = RulePath::parse($path);

		return RulePath::build($array);
	}

}
