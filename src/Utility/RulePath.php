<?php
declare(strict_types=1);

namespace TinyAuthBackend\Utility;

class RulePath {

	/**
	 * @param string $path
	 *
	 * @return array{plugin: string|null, prefix: string|null, controller: string, action: string|null}
	 */
	public static function parse(string $path): array {
		$controller = $path;
		$action = null;
		if (strpos($controller, '::') !== false) {
			[$controller, $action] = explode('::', $controller);
		}

		$prefix = $plugin = null;
		if (strpos($controller, '.') !== false) {
			[$plugin, $controller] = explode('.', $controller, 2);
		}
		if (strpos($controller, '/') !== false) {
			$pos = (int)strrpos($controller, '/');
			$prefix = substr($controller, 0, $pos);
			$controller = substr($controller, $pos + 1);
		}

		return [
			'plugin' => $plugin,
			'prefix' => $prefix ? Utility::underscoreTokenString($prefix, '/') : null,
			'controller' => $controller,
			'action' => $action,
		];
	}

	/**
	 * Full path including action.
	 *
	 * e.g.: MyVendor.MyPrefix/MyController::myAction
	 *
	 * @param array{plugin: string|null, prefix: string|null, controller: string, action: string|null} $array
	 *
	 * @return string
	 */
	public static function build(array $array): string {
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
	 * @param array{plugin: string|null, prefix: string|null, controller: string, action?: string|null} $array
	 *
	 * @return string
	 */
	public static function key(array $array): string {
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
