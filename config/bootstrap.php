<?php
declare(strict_types=1);

use Cake\Core\Configure;

// Default configuration
Configure::write('TinyAuthBackend', [
	'roleHierarchy' => true,
	'multiRole' => false, // Set true for users with multiple roles
	'roleColumn' => 'role_id', // Single role: column name
	'rolesTable' => 'roles', // Multi-role: loaded association/property name on the user entity
	'cacheEnabled' => true,
	'cacheConfig' => 'default',
	'superAdminRole' => null,

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

	// Resource namespace filter for the admin panel
	// Only show resources matching this namespace prefix (e.g., 'App\\' to exclude plugin entities)
	// Set to null or empty string to show all resources
	'resourceNamespaceFilter' => 'App\\',

	// Plugins to exclude from ACL controller tree in admin panel
	// These plugins won't appear in the permission management UI
	'excludedPlugins' => ['DebugKit', 'TinyAuthBackend'],
]);
