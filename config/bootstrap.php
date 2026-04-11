<?php
declare(strict_types=1);

use Cake\Core\Configure;
use Cake\Utility\Hash;

// Default configuration. Merged with any TinyAuthBackend.* values the
// host app already set so callers can pre-seed keys (e.g. editorCheck)
// before the plugin bootstraps without having them wiped.
$defaults = [
	'roleHierarchy' => true,
	'multiRole' => false, // Set true for users with multiple roles
	'roleColumn' => 'role_id', // Single role: column name
	'rolesTable' => 'roles', // Multi-role: loaded association/property name on the user entity
	'cacheEnabled' => true,
	'cacheConfig' => 'default',
	'superAdminRole' => null,

	// Admin UI editor gate. Fail closed by default outside debug mode.
	// In debug=true environments the admin UI stays accessible out of the
	// box for demos / local setup. In debug=false the default denies all
	// access until the host app provides an explicit callable.
	'editorCheck' => static function (mixed $identity, mixed $request): bool {
		return Configure::read('debug') === true;
	},

	// Feature toggles (hybrid: auto-detect from DB tables, override here)
	// null = auto-detect, true = force enable, false = force disable
	'features' => [
		'acl' => null, // Controller/action permissions
		'allow' => null, // Public action management
		'roles' => null, // Role management with hierarchy
		'resources' => null, // Entity-level permissions (for Authorization)
		'scopes' => null, // Conditional permissions (field-based restrictions)
	],

	// Role source configuration
	// Options:
	//   null - Use tinyauth_roles table (default)
	//   'Config.path.to.roles' - Read from Configure::read()
	//   ['admin' => 1, 'user' => 2] - Direct array mapping alias => id
	//   callable - Function returning array of roles
	// Example callable: fn() => TableRegistry::get('Roles')->find('list')->toArray()
	'roleSource' => null,

	// Resource namespace filter for the admin panel.
	// Only show resources whose entity_class starts with this prefix.
	// Null/empty = show all (default). Set to 'App\\' to restrict to
	// main-app entities, or to a plugin namespace (e.g. 'MyPlugin\\')
	// to isolate plugin-owned resources. Note: the previous default
	// of 'App\\' silently excluded plugin entities.
	'resourceNamespaceFilter' => null,

	// Plugins to exclude from sync/admin surfaces.
	// These plugins won't appear in the ACL controller tree and their
	// entities won't be imported into the Resources section.
	'excludedPlugins' => ['DebugKit', 'TinyAuthBackend'],
];

Configure::write(
	'TinyAuthBackend',
	Hash::merge($defaults, (array)Configure::read('TinyAuthBackend')),
);
