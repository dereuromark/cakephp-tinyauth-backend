## TinyAuth Backend

DB backend and adapters for TinyAuth.

These adapters are very simple and in line with the INI files in terms of denormalized structure.
If you need a more normalized DB table approach, create your own ones.

### Enable the plugin

Load the plugin by running
```
bin/cake plugin load TinyAuthBackend
```

or manually add this into your `Application::bootstrap()` method:
```php
    $this->addPlugin('TinyAuthBackend');
```


### Enable the adapters
If you need only Authentication or Authorization or both, add those as needed in your global Configure settings:
```php
'TinyAuth' => [
    'allowAdapter' => \TinyAuthBackend\Auth\AllowAdapter\DbAllowAdapter::class,
    'aclAdapter' => \TinyAuthBackend\Auth\AclAdapter\DbAclAdapter::class,
],
```
Only if those point to the adapters of this plugin, the respective GUI part gets activated.


### Run migration
or provide your own tables.

This uses Migrations plugin:
```
bin/cake migrations migrate -p TinyAuthBackend
```

### Initialize
Add the required ACL rule for the new backend
```
bin/cake tiny_auth_backend init {admin-role-name}
```
This way, you can now access the backend with your admin role.

If you use the `'superAdminRole' => ROLE_SUPERADMIN,` config, this is not necessary, as the
superadmin role has automatically access to all routes.

#### Import
There is a convenience shell command to import existing INI files,
in case you are migrating from file based approach, or if you want
to have some "seed" defaults this way:
```
bin/cake tiny_auth_backend import [allow/acl]
```

You can also directly pass a file to be imported if needed, e.g. for "acl":
```
bin/cake tiny_auth_backend import acl /path/to/file.ini
```

This is useful for batch importing.


### Test run
Navigate to `/admin/auth` backend. It should show the dashboard.

If you didn't include the plugin's default routes, you need to define them similarly on app level.

You can now start adding rules for authentication and authorization.

Note: Every change automatically busts the internal TinyAuth cache.

### Details
See
- [Authentication](Authentication.md) for "allow"
- [Authorization](Authorization.md) for "acl"

### Custom theme/template
TODO
