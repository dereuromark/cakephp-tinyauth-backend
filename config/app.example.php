<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface;

/*
 * Example application-level overrides for TinyAuthBackend.
 *
 * Copy the relevant parts into your app config, e.g. `config/app_local.php`,
 * and adjust as needed.
 */
return [
	'TinyAuthBackend' => [
		/*
		 * Role hierarchy support.
		 *
		 * When enabled, parent roles inherit permissions from descendant roles
		 * where no direct rule exists.
		 */
		'roleHierarchy' => true,

		/*
		 * Multi-role support.
		 *
		 * false: resolve one role from `roleColumn`
		 * true: resolve many roles from the configured association/property
		 */
		'multiRole' => false,

		/*
		 * Single-role mode: integer role id column on the user entity.
		 */
		'roleColumn' => 'role_id',

		/*
		 * Multi-role mode: loaded association/property on the user entity.
		 */
		'rolesTable' => 'roles',

		/*
		 * Internal runtime caching for role/source feature lookups.
		 */
		'cacheEnabled' => true,
		'cacheConfig' => 'default',

		/*
		 * Optional super-admin role alias (or aliases) that bypasses
		 * TinyAuthPolicy / TinyAuthService checks.
		 *
		 * Examples:
		 * 'superAdminRole' => 'admin',
		 * 'superAdminRole' => ['admin', 'root'],
		 */
		'superAdminRole' => null,

		/*
		 * Admin UI editor gate.
		 *
		 * In debug=true the plugin defaults to allowing access for local setup.
		 * In debug=false the plugin defaults to denying access unless you
		 * provide your own callable here.
		 */
		'editorCheck' => static function (mixed $identity, ServerRequestInterface $request): bool {
			if ($identity === null) {
				return false;
			}

			$roleId = is_object($identity) && method_exists($identity, 'get')
				? $identity->get('role_id')
				: ($identity['role_id'] ?? null);

			return (int)$roleId === 3;
		},

		/*
		 * Feature toggles.
		 *
		 * null: auto-detect from database state
		 * true: force-enable
		 * false: force-disable
		 */
		'features' => [
			'acl' => null,
			'allow' => null,
			'roles' => null,
			'resources' => null,
			'scopes' => null,
		],

		/*
		 * Role source configuration.
		 *
		 * Supported forms:
		 * - null: use `tinyauth_roles` table managed by the plugin
		 * - 'Config.path.to.roles': resolve roles from Configure::read()
		 * - ['admin' => 1, 'user' => 2]: direct alias => integer id map
		 * - callable: return the alias => integer id map dynamically
		 */
		'roleSource' => null,

		/*
		 * Restrict the Resources admin UI to entity classes under one namespace.
		 *
		 * Examples:
		 * null
		 * 'App\\'
		 * 'MyPlugin\\'
		 */
		'resourceNamespaceFilter' => null,

		/*
		 * Plugins hidden from the ACL controller tree in the admin UI.
		 */
		'excludedPlugins' => ['DebugKit', 'TinyAuthBackend'],
	],
];
