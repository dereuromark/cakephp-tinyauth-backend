<?php

namespace TinyAuthBackend\Utility;

class RulePath {

	/**
	 * @param string $path
	 *
	 * @return array
	 */
	public static function parse($path) {
		$controller = $path;
		$action = null;
		if (strpos($controller, '::') !== false) {
			list($controller, $action) = explode('::', $controller);
		}

		$prefix = $plugin = null;
		if (strpos($controller, '.') !== false) {
			list($plugin, $controller) = explode('.', $controller, 2);
		}
		if (strpos($controller, '/') !== false) {
			$pos = (int)strrpos($controller, '/');
			$prefix = substr($controller, 0, $pos);
			$controller = substr($controller, $pos + 1);
		}

		return [
			'plugin' => $plugin,
			'prefix' => Utility::underscoreTokenString($prefix, '/'),
			'controller' => $controller,
			'action' => $action,
		];
	}

	/**
	 * Full path including action.
	 *
	 * e.g.: MyVendor.MyPrefix/MyController::myAction
	 *
	 * @param array $array
	 *
	 * @return string
	 */
	public static function build(array $array) {
		$result = $array['controller'];
		if ($array['action']) {
			$result .= '::' . $array['action'];
		}
		if ($array['prefix']) {
			$prefix = Utility::camelizeTokenString($array['prefix'], '/');
			$result = $prefix . '/' . $result;
		}
		if ($array['plugin']) {
			$result = $array['plugin'] . '.' . $result;
		}

		return $result;
	}

	/**
	 * Generates the key as controller without action.
	 *
	 * e.g.: MyVendor.my_prefix/MyController
	 *
	 * @param array $array
	 *
	 * @return string
	 */
	public static function key(array $array) {
		$result = $array['controller'];
		if ($array['prefix']) {
			$prefix = Utility::underscoreTokenString($array['prefix'], '/');
			$result = $prefix . '/' . $result;
		}
		if ($array['plugin']) {
			$result = $array['plugin'] . '.' . $result;
		}

		return $result;
	}

}
